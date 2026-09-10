<?php
/**
 * Dish Image Handler
 * Sistema integrato per recuperare immagini di piatti da API esterne
 * Supporta: Spoonacular, Pixabay, Pexels, Unsplash
 */

class DishImageHandler {
    
    // API Keys (configurable)
    private $spoonacularKey = 'YOUR_SPOONACULAR_KEY'; // Get from https://spoonacular.com/food-api
    private $pixabayKey = 'YOUR_PIXABAY_KEY'; // Get from https://pixabay.com/api/docs/
    private $unsplashKey = 'YOUR_UNSPLASH_KEY'; // Get from https://unsplash.com/developers
    
    private $cacheDir = '../../../cache/dish-images/';
    private $cacheDuration = 2592000; // 30 giorni
    
    public function __construct() {
        if (!is_dir($this->cacheDir)) {
            mkdir($this->cacheDir, 0755, true);
        }
    }
    
    /**
     * Ottiene immagine per un piatto
     * @param string $dishName Nome del piatto
     * @param string $ingredients Ingredienti (comma separated)
     * @param string $category Categoria (Pizza, Pasta, etc)
     * @return array|false URL immagine o false se non trovata
     */
    public function getImageForDish($dishName, $ingredients = '', $category = '') {
        
        // 1. Controlla cache
        $cacheKey = md5(strtolower($dishName));
        $cached = $this->getCachedImage($cacheKey);
        if ($cached) {
            return [
                'url' => $cached,
                'source' => 'cache',
                'success' => true
            ];
        }
        
        // 2. Prova Spoonacular (migliore per piatti italiani)
        if (!empty($this->spoonacularKey)) {
            $result = $this->searchSpoonacular($dishName, $ingredients);
            if ($result) {
                $this->cacheImage($cacheKey, $result['url']);
                return $result;
            }
        }
        
        // 3. Prova Pixabay (grande database, veloce)
        if (!empty($this->pixabayKey)) {
            $result = $this->searchPixabay($dishName, $ingredients, $category);
            if ($result) {
                $this->cacheImage($cacheKey, $result['url']);
                return $result;
            }
        }
        
        // 4. Prova Unsplash (alta qualità)
        if (!empty($this->unsplashKey)) {
            $result = $this->searchUnsplash($dishName, $ingredients);
            if ($result) {
                $this->cacheImage($cacheKey, $result['url']);
                return $result;
            }
        }
        
        // 5. Fallback: genera URL da Placeholder
        return $this->getFallbackImage($dishName, $category);
    }
    
    /**
     * Cerca in Spoonacular API
     * API specializzata in ricette e immagini di piatti
     */
    private function searchSpoonacular($dishName, $ingredients) {
        try {
            $query = urlencode($dishName);
            $url = "https://api.spoonacular.com/recipes/complexSearch?" .
                   "query={$query}" .
                   "&addRecipeInformation=true" .
                   "&number=1" .
                   "&apiKey={$this->spoonacularKey}";
            
            $response = $this->fetchUrl($url);
            if (!$response) return false;
            
            $data = json_decode($response, true);
            if (!isset($data['results']) || empty($data['results'])) {
                return false;
            }
            
            $recipe = $data['results'][0];
            if (!isset($recipe['image'])) return false;
            
            return [
                'url' => $recipe['image'],
                'source' => 'spoonacular',
                'success' => true,
                'title' => $recipe['title'] ?? $dishName
            ];
        } catch (Exception $e) {
            return false;
        }
    }
    
    /**
     * Cerca in Pixabay API
     * Grande database di immagini gratuite
     */
    private function searchPixabay($dishName, $ingredients, $category) {
        try {
            // Costruisci query migliore
            $searchTerms = [$dishName];
            if (!empty($category)) {
                $searchTerms[] = $category;
            }
            if (!empty($ingredients)) {
                $ingr = explode(',', $ingredients);
                $searchTerms[] = trim($ingr[0]); // Primo ingrediente
            }
            
            $query = urlencode(implode(' ', $searchTerms));
            
            $url = "https://pixabay.com/api/" .
                   "?q={$query}" .
                   "&image_type=photo" .
                   "&per_page=3" .
                   "&safesearch=true" .
                   "&order=popular" .
                   "&key={$this->pixabayKey}";
            
            $response = $this->fetchUrl($url);
            if (!$response) return false;
            
            $data = json_decode($response, true);
            if (!isset($data['hits']) || empty($data['hits'])) {
                return false;
            }
            
            // Prendi immagine con qualità migliore
            $image = $data['hits'][0];
            return [
                'url' => $image['largeImageURL'] ?? $image['webformatURL'],
                'source' => 'pixabay',
                'success' => true,
                'photographer' => $image['user'] ?? 'Unknown'
            ];
        } catch (Exception $e) {
            return false;
        }
    }
    
    /**
     * Cerca in Unsplash API
     * Immagini ad alta risoluzione
     */
    private function searchUnsplash($dishName, $ingredients) {
        try {
            $query = urlencode($dishName);
            $url = "https://api.unsplash.com/search/photos" .
                   "?query={$query}" .
                   "&per_page=1" .
                   "&orientation=landscape" .
                   "&Client-ID={$this->unsplashKey}";
            
            $response = $this->fetchUrl($url);
            if (!$response) return false;
            
            $data = json_decode($response, true);
            if (!isset($data['results']) || empty($data['results'])) {
                return false;
            }
            
            $image = $data['results'][0];
            return [
                'url' => $image['urls']['regular'],
                'source' => 'unsplash',
                'success' => true,
                'photographer' => $image['user']['name'] ?? 'Unknown'
            ];
        } catch (Exception $e) {
            return false;
        }
    }
    
    /**
     * Ottieni immagine da cache
     */
    private function getCachedImage($cacheKey) {
        $cacheFile = $this->cacheDir . $cacheKey . '.json';
        
        if (file_exists($cacheFile)) {
            $cached = json_decode(file_get_contents($cacheFile), true);
            
            // Controlla se ancora valida
            if (time() - $cached['timestamp'] < $this->cacheDuration) {
                return $cached['url'];
            } else {
                unlink($cacheFile);
            }
        }
        
        return false;
    }
    
    /**
     * Salva immagine in cache
     */
    private function cacheImage($cacheKey, $imageUrl) {
        $cacheFile = $this->cacheDir . $cacheKey . '.json';
        $data = [
            'url' => $imageUrl,
            'timestamp' => time()
        ];
        file_put_contents($cacheFile, json_encode($data));
    }
    
    /**
     * Fallback: Placeholder Service (UI Avatars, DiceBear, etc)
     */
    private function getFallbackImage($dishName, $category) {
        // Usa un servizio di placeholder elegante
        // Option 1: Placeholder con colori per categoria
        $colors = [
            'pizza' => 'FF6B6B',
            'pasta' => 'FFD93D',
            'piatti' => '6BCB77',
            'dolci' => 'E8B4B8',
            'bevande' => '4D96FF',
            'antipasti' => 'A8D8EA',
            'secondi' => 'F0A6CA',
            'insalate' => '95E1D3'
        ];
        
        $color = $colors[strtolower($category)] ?? 'CCCCCC';
        
        // UI Avatars Placeholder (genera colori automatici)
        $initials = substr($dishName, 0, 2);
        $placeholderUrl = "https://ui-avatars.com/api/" .
                         "?name=" . urlencode($initials) .
                         "&background=" . $color .
                         "&color=fff" .
                         "&size=300" .
                         "&bold=true";
        
        return [
            'url' => $placeholderUrl,
            'source' => 'placeholder',
            'success' => false,
            'fallback' => true
        ];
    }
    
    /**
     * Fetch URL con timeout
     */
    private function fetchUrl($url) {
        $ch = curl_init($url);
        curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
        curl_setopt($ch, CURLOPT_TIMEOUT, 10);
        curl_setopt($ch, CURLOPT_SSL_VERIFYPEER, false);
        curl_setopt($ch, CURLOPT_USERAGENT, 'trovapiatto/1.0');
        
        $response = curl_exec($ch);
        $httpCode = curl_getinfo($ch, CURLINFO_HTTP_CODE);
        curl_close($ch);
        
        if ($httpCode === 200) {
            return $response;
        }
        return false;
    }
    
    /**
     * Batch: Ottieni immagini per più piatti
     */
    public function getImagesForDishes($dishes) {
        $results = [];
        foreach ($dishes as $dish) {
            $results[] = [
                'id' => $dish['id'] ?? uniqid(),
                'name' => $dish['name'],
                'image' => $this->getImageForDish(
                    $dish['name'],
                    $dish['ingredients'] ?? '',
                    $dish['category'] ?? ''
                )
            ];
        }
        return $results;
    }
}

/**
 * Endpoint API per ricerca immagini
 */
if ($_SERVER['REQUEST_METHOD'] === 'GET' && isset($_GET['action']) && $_GET['action'] === 'get-dish-image') {
    
    $handler = new DishImageHandler();
    
    if (isset($_GET['name'])) {
        $image = $handler->getImageForDish(
            $_GET['name'],
            $_GET['ingredients'] ?? '',
            $_GET['category'] ?? ''
        );
        
        header('Content-Type: application/json');
        echo json_encode($image);
        exit;
    }
    
    header('Content-Type: application/json');
    echo json_encode(['success' => false, 'error' => 'Missing dish name']);
    exit;
}

// Batch endpoint
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_GET['action']) && $_GET['action'] === 'get-dishes-images') {
    
    $input = json_decode(file_get_contents('php://input'), true);
    
    if (!isset($input['dishes']) || !is_array($input['dishes'])) {
        header('Content-Type: application/json');
        echo json_encode(['success' => false, 'error' => 'Invalid input']);
        exit;
    }
    
    $handler = new DishImageHandler();
    $results = $handler->getImagesForDishes($input['dishes']);
    
    header('Content-Type: application/json');
    echo json_encode([
        'success' => true,
        'data' => $results
    ]);
    exit;
}
?>

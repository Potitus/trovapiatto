<?php
require_once __DIR__ . '/config.php';
tp_require_setup_access();
if (isset($_GET['run']) && $_GET['run'] === 'ib') {
$pdo=tp_db_connect();
$pdo->setAttribute(PDO::ATTR_ERRMODE,PDO::ERRMODE_EXCEPTION);
$s=$pdo->prepare("SELECT id FROM restaurants WHERE slug=?");$s->execute(['beccheria-del-borgo-antico']);$r=$s->fetch();$rid=$r['id'];
$pdo->prepare("DELETE FROM dishes WHERE restaurant_id=?")->execute([$rid]);
$d=[['cat_bec_ap','Antipasto della casa',15],['cat_bec_pr','Orecchiette e rape',13],['cat_bec_pr','Orecchiette con la braciola',15],['cat_bec_pr','Tortellone ripieno di braciola (3pz)',13],['cat_bec_se','Grigliata mista',35],['cat_bec_se','Bistecche (taglio Fiorentina)',50],['cat_bec_se','Braciola (1 pz)',8],['cat_bec_pa','Brasciola',6],['cat_bec_pa','Salsiccia',5],['cat_bec_pa','Zampina',6],['cat_bec_pa','Falda',7],['cat_bec_pa','Hamburger',5],['cat_bec_pa','Filetto di pollo',5],['cat_bec_pa','Entrecote',7],['cat_bec_pa','Bombette pugliesi',6],['cat_bec_pa','Tagliata',7],['cat_bec_pa','Porchetta',6],['cat_bec_pa','Norcia (piccante/dolce)',5],['cat_bec_co','Patatine fritte (vaschetta piccola)',3.5],['cat_bec_co','Patatine fritte (vaschetta media)',4.5],['cat_bec_co','Patatine fritte (vaschetta grande)',5.5],['cat_bec_be','Coca-Cola / Zero / Fanta / Sprite (0.33L)',3],['cat_bec_be','Acqua Lilia / Sveva (1L)',4],['cat_bec_be','Vino della casa (1L)',12],['cat_bec_be','Vino della casa (0.5L)',6],['cat_bec_be','Birra Peroni (0.33L)',3],['cat_bec_do','Bomboniere',5],['cat_bec_do','Pastiera di ricotta',4],['cat_bec_do','Liquori della casa (bicchierino)',2]];
$n=0;foreach($d as $x){$id=uniqid('d_');$p=$pdo->prepare("INSERT INTO dishes(id,restaurant_id,category,name,price,available,allergens)VALUES(?,?,?,?,1,'[]')");$p->execute([$id,$rid,$x[0],$x[1],$x[2]]);$n++;}
echo "OK: $n dishes imported";
} else { echo "test ok"; }

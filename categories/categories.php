<?php
if(!defined('PLX_ROOT')) {
	die('Are you silly ?');
}

class categories extends plxPlugin {

	public $aCats = array();

	const HOOKS = array(
		'plxShowLastCatList',
		'plxAdminEditCategoriesNew',
		'plxAdminEditCategoriesUpdate',
		'plxAdminEditCategoriesXml',
		'plxMotorGetCategories',
		'plxAdminEditCategorie',
		// 'AdminCategoryTop',
		'AdminCategoriesTop',
		'AdminCategory',
		// 'AdminTopMenus',
		'AdminCategoriesPrepend',
		'AdminArticlePrepend',
	);
	const BEGIN_CODE = '<?php' . PHP_EOL;
	const END_CODE = PHP_EOL . '?>';

	public function __construct($default_lang) {
		# appel du constructeur de la classe plxPlugin (obligatoire)
		parent::__construct($default_lang);

		# Ajoute des hooks
		foreach(self::HOOKS as $hook) {
			$this->addHook($hook, $hook);
		}

	}

	public function plxShowLastCatList() {

		echo self::BEGIN_CODE;
?>
if (($this->plxMotor->aCats) && ($this->plxMotor->mode !== 'static') ) {

	$currentCats = $this->catId(true);

	if ($this->plxMotor->mode === 'home' ) $currentCats[]='';


	#Initialisation du tableau de la collection des numeros de categories a rechercher
	$keySearch = array();


	#recherche de la catégorie affichée ou les categories de l'article
	foreach($currentCats as $catKey) {
		array_push( $keySearch, $catKey) ;
		$cat_to_set[]=$catKey;
		}
	#fin recherche categorie affichée

	#debug : recherche et affichage nombre catégories dans la collection
	$keySearchCount = count(array_column($keySearch, null));



	#on verifie si c'est une page catègorie et si celle ci est mother ou daughter Of .
	if(	$this->plxMotor->mode === 'categorie') {
		if ($this->plxMotor->aCats[ $keySearch[0]]['daughterOf'] != '000'){
			array_push($keySearch,  $this->plxMotor->aCats[ $keySearch[0]]['daughterOf']);
			array_push($cat_to_set, $this->plxMotor->aCats[ $keySearch[0]]['daughterOf']);
		}

	}

	#pour l'instant, on ne sait pas si il y a une catégorie avec le statut : mother a 1.
	$okay=false;

	#y aura t-il des soeurs ?
	$sister='';

	#on regarde si on est en preview et si l'on a plus d'une categorie soeur et on alimente le tableau.
	if((!isset($_GET['preview'])) && ($keySearchCount === 1 )) {
			$sister= $this->plxMotor->aCats[ $keySearch[0]]['daughterOf'];
			$cat_to_set[]=$sister;
	} else {
		$keySearch[]='000';
	}

	#boucle sur les catégories
	foreach(array_keys($this->plxMotor->aCats) as $array_key) {

		#on recherche si l'on a des categorie avec le statut mother a 1 si vrai alors okay est true :)
		if ($this->plxMotor->aCats[$array_key]['mother'] ==='1') {
			$okay=true;
		}

		#ajout espace blanc braille devant le nom de catégorie pour un effet visuel d'indentation à l'affichage
		if($this->plxMotor->aCats[$array_key]['daughterOf'] !== '000') 	$this->plxMotor->aCats[$array_key]['name'] = '⠀'.$this->plxMotor->aCats[$array_key]['name'];

		#préremplissage liste catégories a retirées. nettoyage en fin de script si okay est true.
		$cats_found[]=$array_key;


		#si c'est une categorie fille, on ajoute sa mere.
		if (($this->plxMotor->aCats[$array_key]['daughterOf'] === $sister ) && ($this->plxMotor->aCats[$array_key]['daughterOf'] !== '000') ){
				$cat_to_set[]=$array_key;
				$okay=true;
			}

		#recherche de valeur de clé correspondant a une valeur de $keySearch  pour alimenter la collection à l'affichage
		foreach($keySearch as $keytest => $ask ) {
			if(preg_match("/\b$ask\b/i", $this->plxMotor->aCats[$array_key]['daughterOf'])){
					$cat_to_set[]=$array_key;
			}
		}#fin ajout clé

	}


	#Si l'on a trouvé au moins une categorie mere , faire le tri des categories à afficher , sinon on a bosser pour rien !
	if($okay) {
		$cat_to_remove = array_diff( $cats_found,$cat_to_set);
		foreach($cat_to_remove as $unset) {
			unset($this->plxMotor->aCats[$unset]);
		}
	}
}

#indentation visuel des categorie fille
if (($this->plxMotor->aCats) && (	$this->plxMotor->mode === 'static')){
	#boucle sur les catégories
	foreach(array_keys($this->plxMotor->aCats) as $array_key) {
		#ajout espace blanc braille devant le nom de catégorie pour un effet visuel d'indentation à l'affichage
			if($this->plxMotor->aCats[$array_key]['daughterOf'] !== '000') 	$this->plxMotor->aCats[$array_key]['name'] = '⠀'.$this->plxMotor->aCats[$array_key]['name'];
	}
}

###################################################################################################################################################################################
#                                                                         ╔═════════════╗                                                                                         #
#                                                                         ║ DISCLAIMER: ║                                                                                         #
#                                                                         ╚═════════════╝                                                                                         #
#Pour le moment:version 1 du 26/03/2021 par GC-nomade AKA gcyrillus.                                                                                  #
#Si au moins une catégorie mére a été trouvée, seules les catégories contenant au moins un article et rattachées a(ux) la catégorie(s) mere filtrée(s) devrai(en)t etre affichée(s)#
# ...dans la sidebar.                                                                                                                                                             #
# pas toisé => comportement avec une seule catégorie mother='1' ?                                                                                                                 #
# pas toisé => categorie orphelines ?                                                                                                                                             #
# articles rattachés à une catégorie orpheline ?                                                                                                                                  #
# pas toisé => toutes suggestion sont les bienvenues, La programmation n'est pas mon domaine.                                                                                     #
# pas vu encore : filtrage des tags selon categorie(s) méres                                                                                                                      #
# compatibilité avec d'autre plugins ? infos a remonté par les utilisateurs.                                                                                  #
###################################################################################################################################################################################

<?php

		echo self::END_CODE;
	}

	#ajout des champs mother et daughterOf dans le tableau  nouvelle catégorie
	public function plxAdminEditCategoriesNew() {
		echo self::BEGIN_CODE;
?>
$this->aCats[$content['new_catid']]['mother'    ] = '0';
$this->aCats[$content['new_catid']]['daughterOf'] = '';
<?php
		echo self::END_CODE;
	}

	#recuperation des valeurs mother et daughterOf pour l'edition
	public function plxAdminEditCategoriesUpdate() {
		echo self::BEGIN_CODE;
?>
$this->aCats[$cat_id]['mother'    ] = $content[$cat_id.'_mother'];
$this->aCats[$cat_id]['daughterOf'] = $content[$cat_id.'_daughterOf'];
<?php
		echo self::END_CODE;
	}
	#ajout des attributs mother et daughterOf aux tags <categorie> dans le fichier categories.xmol
	public function plxAdminEditCategoriesXml() {
		echo self::BEGIN_CODE;
?>

$mother= $cat['mother'];
$daughterOf= $cat['daughterOf'];
$attr1 = ' mother="'.$mother.'" ';
$attr2 = ' daughterOf="'.$daughterOf.'" ';
$search='<categorie ';
$xml = preg_replace('~(.*)' . preg_quote($search, '~') . '~su', '${1}'.$search.$attr1.$attr2, $xml);

<?php

		echo self::END_CODE;
	}

	#recuperation et ajout des valeurs des attributs mother et daughterOf.
	public function plxMotorGetCategories() {
		echo self::BEGIN_CODE;
?>
$this->aCats[$number]['mother']    =isset($attributes['mother'])     ? $attributes['mother']:'0';
$this->aCats[$number]['daughterOf']=isset($attributes['daughterOf']) ? $attributes['daughterOf']:'';
<?php
		echo self::END_CODE;
	}

	#nettoyage des valeurs de mother et daughterOf
	public function plxAdminEditCategorie() {
		echo self::BEGIN_CODE;
?>
$this->aCats[$content['id']]['mother']     = trim($content['mother']);
$this->aCats[$content['id']]['daughterOf'] = trim($content['daughterOf']);
<?php
		echo self::END_CODE;
	}

	#remplissage des valeur pour le select affichant les categories 'daughterOf', par défaut 'orpheline' à l'affichage
	public function AdminCategoriesTop() {
		echo self::BEGIN_CODE;
?>
#remplissage des select fille
if($plxAdmin->aCats) {
	$MotherArray['000']='orpheline';//defaut
		foreach($plxAdmin->aCats as $key=>$value) {//boucle si il y a des catégories meres.
			if($value['mother']==="1"){
				$MotherArray[$key]=$value['name'];// on rempli le tableau
			}
		}
}
<?php
		echo self::END_CODE;
	}

	public function AdminCategory() {
	}

	#remplacement du lien d'edition des categories categories.php par plg_categories.php ou sont ausssi affichés les champs(select) de mother et daughterOf
	public function AdminTopMenus() {
		echo self::BEGIN_CODE;
?>
#remplace la page categories.php par celle du plugin
$search = 'categories.php';
foreach($menus as $k=>$v) {
	if(preg_match("/\b$search\b/i", $v)) {
		$menus[$k] = $v;
		$v = str_replace($search, PLX_ROOT.'plugins/categories/plg_categories.php', $v);
		$menus[$k] = $v;
	}
}


#remplace la page article.php par celle du plugin
$search = 'article.php';
foreach($menus as $k=>$v) {
	if(preg_match("/\b$search\b/i", $v)) {
		$menus[$k] = $v;
		$v = str_replace($search, PLX_ROOT.'plugins/categories/plg_article.php', $v);
		$menus[$k] = $v;
	}
}
<?php
		echo self::END_CODE;
	}

	#mise a jour du renvoi vers la page plg_categories.php au lieu de categories.php si le plugin est actif
	public function AdminCategoriesPrepend() {
		echo self::BEGIN_CODE;
?>
$plgPlugin = $plxAdmin->plxPlugins->aPlugins['<?= __CLASS__ ?>'];
include PLX_PLUGINS . '<?= __CLASS__ ?>/plg_categories.php';
exit;
<?php
		echo self::END_CODE;
	}

	public function AdminArticlePrepend() {
		echo self::BEGIN_CODE;
?>
$plgPlugin = $plxAdmin->plxPlugins->aPlugins['<?= __CLASS__ ?>'];
include PLX_PLUGINS . '<?= __CLASS__ ?>/plg_article.php';
exit;
<?php
		echo self::END_CODE;
	}
}

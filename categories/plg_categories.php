<?php

/**
 * Edition des catégories
 *
 * @package PLX
 * @author	Stephane F et Florent MONTHEL
 **/

include __DIR__ .'/../../core/admin/prepend.php';

# Control du token du formulaire
plxToken::validateFormToken($_POST);

# Hook Plugins
#eval($plxAdmin->plxPlugins->callHook('AdminCategoriesPrepend'));

# Control de l'accès à la page en fonction du profil de l'utilisateur connecté
$plxAdmin->checkProfil(PROFIL_ADMIN, PROFIL_MANAGER, PROFIL_MODERATOR, PROFIL_EDITOR);

# On édite les catégories
if(!empty($_POST)) {
	$plxAdmin->editCategories($_POST);
	header('Location: plg_categories.php');
	exit;
}

# Tableau du tri
$aTri = array(
	'desc'		=> L_SORT_DESCENDING_DATE,
	'asc'		=> L_SORT_ASCENDING_DATE,
	'alpha'		=> L_SORT_ALPHABETICAL,
	'ralpha'	=> L_SORT_REVERSE_ALPHABETICAL,
	'random'	=> L_SORT_RANDOM
);

# On inclut le header
include __DIR__ .'/../../core/admin/top.php';

?>
<style>
tr{
	--mother_001:#90EE90;
	--daughterOf_001:#90EE90;
	--mother_002:#ADD8E6;
	--daughterOf_002:#ADD8E6;
	--mother_003:#FFD700;
	--daughterOf_003:#FFD700 ;
	--mother_004:#FF6347;
	--daughterOf_004:#FF6347 ;
	--mother_005:#6856ba;
	--daughterOf_005:#6856ba;
	--mother_006:#e66043;
	--daughterOf_006:#e66043;
	--mother_007:#23f74a;
	--daughterOf_007:#23f74a;
	--mother_008:#069420;
	--daughterOf_008:#069420;
	--mother_009:#543dba;
	--daughterOf_009:#543dba;
	--mother_010:#2102ad;
	--daughterOf_010:#2102ad;
 	--mother_011:#2f7a3c;
	--daughterOf_011:#2f7a3c;
	--mother_012:#ff6f4f;
	--daughterOf_012:#ff6f4f;
	--mother_013:#065c16;
	--daughterOf_013:#065c16;
	--mother_014:#26913a;
	--daughterOf_014:#26913a;
}</style>
<form action="" method="post" id="form_categories">

	<div class="inline-form action-bar">
		<h2><?php echo L_CAT_TITLE ?></h2>
		<p><a class="back" href="<?php echo PLX_ROOT ?>core/admin/index.php"><?php echo L_BACK_TO_ARTICLES ?></a></p>
		<?php plxUtils::printSelect('selection', array( '' => L_FOR_SELECTION, 'delete' => L_DELETE), '', false, 'no-margin', 'id_selection') ?>
		<input type="submit" name="submit" value="<?php echo L_OK ?>" onclick="return confirmAction(this.form, 'id_selection', 'delete', 'idCategory[]', '<?php echo L_CONFIRM_DELETE ?>')" />
		<?php echo plxToken::getTokenPostMethod() ?>
		<span class="sml-hide med-show">&nbsp;&nbsp;&nbsp;</span>
		<input type="submit" name="update" value="<?php echo L_CAT_APPLY_BUTTON ?>" />
	</div>

	<?php eval($plxAdmin->plxPlugins->callHook('AdminCategoriesTop')) # Hook Plugins ?>



	<div class="scrollable-table">
		<table id="categories-table" class="full-width" data-rows-num='name$="_ordre"'>
			<thead>
				<tr>
					<th class="checkbox"><input type="checkbox" onclick="checkAll(this.form, 'idCategory[]')" /></th>
					<th><?php echo L_ID ?></th>
					<th><?php echo L_CAT_LIST_NAME ?></th>
					<th><?php echo L_CAT_LIST_URL ?></th>
					<th>Mere</th>
					<th>Fille</th>
					<th><?php echo L_CAT_LIST_ACTIVE ?></th>
					<th><?php echo L_CAT_LIST_SORT ?></th>
					<th><?php echo L_CAT_LIST_BYPAGE ?></th>
					<th data-id="order"><?php echo L_CAT_LIST_ORDER ?></th>
					<th><?php echo L_CAT_LIST_MENU ?></th>
					<th>&nbsp;</th>
				</tr>
			</thead>
			<tbody>
			<?php
			# Initialisation de l'ordre
			$ordre = 1;
			# Si on a des catégories
			if($plxAdmin->aCats) {
				foreach($plxAdmin->aCats as $k=>$v) { # Pour chaque catégorie
					$data="cat";
					if($v['mother']=="1"){ $data="mother";}
					echo '<tr style="background:linear-gradient(90deg,transparent 6em,  var(--daughterOf_'.$v['daughterOf'].',rgba(0,0,0,0)) 6em, var(--daughterOf_'.$v['daughterOf'].', rgba(0,0,0,0) ))  var(--'.$data.'_'.$k.',rgba(0,0,0,0)) ;">';
					echo '<td><input type="checkbox" name="idCategory[]" value="'.$k.'" /><input type="hidden" name="catNum[]" value="'.$k.'" /></td>';
					echo '<td>'.$k.'</td><td>';
					plxUtils::printInput($k.'_name', plxUtils::strCheck($v['name']), 'text', '-50');
					echo '</td><td>';
					plxUtils::printInput($k.'_url', $v['url'], 'text', '-50');
					echo '</td><td>';
					plxUtils::printSelect($k.'_mother',      array('1'=>L_YES,'0'=>L_NO), $v['mother']);
					echo '</td><td>';
					plxUtils::printSelect($k.'_daughterOf', $MotherArray, $v['daughterOf']);
					echo'</td><td>';
					plxUtils::printSelect($k.'_active',      array('1'=>L_YES,'0'=>L_NO), $v['active']);
					echo '</td><td>';
					plxUtils::printSelect($k.'_tri', $aTri, $v['tri']);
					echo '</td><td>';
					plxUtils::printInput($k.'_bypage', $v['bypage'], 'text', '-3');
					echo '</td><td>';
					plxUtils::printInput($k.'_ordre', $ordre, 'text', '-3');
					echo '</td><td>';
					plxUtils::printSelect($k.'_menu', array('oui'=>L_DISPLAY,'non'=>L_HIDE), $v['menu']);
					echo '</td>';
					echo '<td><a href="'.PLX_ROOT.'core/admin/categorie.php?p='.$k.'">'.L_OPTIONS.'</a></td>';
					echo '</tr>';
					$ordre++;
				}
				# On récupère le dernier identifiant
				$a = array_keys($plxAdmin->aCats);
				rsort($a);
			} else {
				$a['0'] = 0;
			}
			$new_catid = str_pad($a['0']+1, 3, "0", STR_PAD_LEFT);
			?>
				<tr class="new">
					<td colspan="2"><?php echo L_NEW_CATEGORY ?></td>
					<td>
					<?php
						echo '<input type="hidden" name="catNum[]" value="'.$new_catid.'" />';
						plxUtils::printInput($new_catid.'_template', 'categorie.php', 'hidden');
						plxUtils::printInput($new_catid.'_name', '', 'text', '-50');
						echo '</td><td>';
						plxUtils::printInput($new_catid.'_url', '', 'text', '-50');
						echo '</td><td>';
						plxUtils::printSelect($new_catid.'_mother', array('1'=>L_YES,'0'=>L_NO), '0');
						echo '</td><td>';
						plxUtils::printSelect($new_catid.'_daughterOf', $MotherArray, '000');
						echo '</td><td>';
						plxUtils::printSelect($new_catid.'_active', array('1'=>L_YES,'0'=>L_NO), '1');
						echo '</td><td>';
						plxUtils::printSelect($new_catid.'_tri', $aTri, $plxAdmin->aConf['tri']);
						echo '</td><td>';
						plxUtils::printInput($new_catid.'_bypage', $plxAdmin->aConf['bypage'], 'text', '-3');
						echo '</td><td>';
						plxUtils::printInput($new_catid.'_ordre', $ordre, 'text', '-3');
						echo '</td><td>';
						plxUtils::printSelect($new_catid.'_menu', array('oui'=>L_DISPLAY,'non'=>L_HIDE), '1');
						echo '</td><td>&nbsp;';
					?>
					</td>
				</tr>
			</tbody>
		</table>
	</div>

</form>

<?php
# Hook Plugins
eval($plxAdmin->plxPlugins->callHook('AdminCategoriesFoot'));
# On inclut le footer
include __DIR__ .'/../../core/admin/foot.php';
?>
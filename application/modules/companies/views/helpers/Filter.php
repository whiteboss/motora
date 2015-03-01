<?php
/**
 * 
 * Помошник вида: фильтр
 */

class Companies_View_Helper_Filter extends Zend_View_Helper_Abstract {
	
	public function filter() {
		$this->view->headScript()
			->appendFile( '/resources/jquery/js/jquery.form.js' )
			->appendFile( '/js/companies/filter.js' );
		if ( empty( $this->view->catalog ) ) return '';
		$spheres = array();
		foreach( $this->view->catalog as $sphere_id => $sphere ) {
			$activities = array();
			foreach( $sphere['subs'] as $id => $activity ) {
				$activities[] = '<label for="industry-'.$id.'"><input type="checkbox" name="industry[]" id="industry-'.$id.'" value="'.$id.'">'.$activity.'</label>';
			}
			if ( !empty($activities) )
				$spheres[] = '<li><a href="#">'.$sphere['name'].'</a><div>'.implode("<br />\n",$activities).'</div></li>';
		}
		$output = '
<style>
label.active {
	color: red;
}
</style>
<div id="filter" style="float: left; width: 440px">
	<form action="/companies/company/filter" method="post">
	<ul id="activities" class="sliding">
	' . implode("\n", $spheres) . '
	</ul>
	</form>
</div>';
		return $output;
	}
}
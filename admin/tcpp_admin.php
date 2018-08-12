<?

class TCPP_Admin extends TCPP{
	const TCPP_ADMIN_JS = TCPP_PLUGIN_URL . '/admin/view/js/';
	const TCPP_ADMIN_CSS = TCPP_PLUGIN_URL . '/admin/view/css/';
	
	public function get_page(){
		$class = ($_GET['page'] != 'mc_admin_home') ? $_GET['page'] : 'tcpp_parser';
		
		require_once TCPP_PLUGIN_DIR . '/admin/controllers/' . $class . '.php';
		
		$page = new $class();
		$action = (isset($_GET['action'])) ? $_GET['action'] : 'view';

		$data = array();
		$data['atts'] = $atts;
		$data['content'] = $content;
		$data['tag'] = $tag;
		
		return $page->$action($data);
	}
	
	protected function get_model($model){
		require_once TCPP_PLUGIN_DIR . '/admin/models/' . $model . '.php';
		
		return new $model();
	}
	
	protected function get_action_link($action, $element = false){
		$url = 'edit.php?post_type=tcpc&page=tcpp_parser&action=' . $action;
		
		if(!empty($element)){
			foreach($element as $key => $value){
				$url .= '&' . $key . '=' . $value;
			}
		}
		
		return admin_url($url);
	}
}
?>
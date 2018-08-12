<?

class TCPP{
	protected $db;
	protected $plugin_dir;
	protected $prefix;
	
	public function __construct(){
		global $wpdb;
		
		$this->db = $mydb;
		$this->plugin_dir = plugin_dir_url( __FILE__ );
		$this->prefix = $wpdb->prefix;
	}
	
	public function run(){
		if(is_admin()){
			require_once TCPP_PLUGIN_DIR . '/admin/tcpp_admin.php';
			
			$this->run_admin();
			wp_enqueue_style('tcpp_style_admin_css', TCPP_PLUGIN_URL . '/admin/view/css/tcpp_parser.css');
			wp_enqueue_script('tcpp_admin_js', TCPP_PLUGIN_URL . '/view/admin/js/tcpp_parser.js');
		} else {
			return new WP_Error('no_admin','Пользователь не является админом');
		}
	}
	
	public function run_admin(){
		add_action('admin_menu', array($this, 'add_menu_admin'));
		
		return true;
	}
	
	public function add_menu_admin(){
		$admin_class = new TCPP_Admin();
		
		add_submenu_page('edit.php?post_type=tcpc','Парсер','Парсер', 5,'tcpp_parser',array('TCPP_Admin', 'get_page'));
	}
	
	protected function render($template, $data = null){
		$file = $template;
		
		if (file_exists($file)) {
			if(isset($data)) extract($data);
			
			ob_start();

			require($file);

			$output = ob_get_contents();

			ob_end_clean();
		} else {
			echo '<h3>Отсутствует файл шаблона - <b>' . $file . '</b>!</h3>';
			exit();
		}

		return $output;
	}

	protected function pre_print($data){
		echo '<pre>';
		print_r($data);
		echo '</pre>';
	}
}
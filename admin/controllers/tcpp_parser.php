<?

class TCPP_Parser extends TCPP_Admin{
	public function view(){
		$data = array();
		$data['title'] = 'Парсер';

		$data['get_link'] = $this->get_action_link('parser', array('parsing_page'=>1));

		$data['term'] = WP_CONTENT_URL;

		$title = 'Дуб Цермат глинистый';
		$query = new WP_Query;
		$d = $query->query(array('name'=>$title, 'post_type'=>'tcpc'));
		$data['data'] = (empty($d)) ? false : $d;

		echo $this->render(TCPP_ADMIN_TPL . 'page/parser/view.tpl', $data);
	}

	public function parser(){
		$data = array();
		$data['title'] = 'Парсер';

		$parse_page = (!empty($_GET['parsing_page'])) ? $_GET['parsing_page'] : 1;
		$data['get_link'] = $this->get_action_link('parser', array('parsing_page'=>($parse_page + 1)));

		$parser = $this->get_model('parser');

		$parser->url = 'http://kmetr-m2.ru';
		$parser->page = $parse_page;
		$parser->parsing();
		$data['parser_result'] = $parser->get_result();

		echo $this->render(TCPP_ADMIN_TPL . 'page/parser/parser.tpl', $data);
	}
}
?>
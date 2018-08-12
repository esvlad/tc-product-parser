<?

class Parser extends TCPP_Admin{
	public $url;
	public $page;
	private $products;
	private $result;

	private function set_products(){
		require_once TCPP_PLUGIN_DIR . '/includes/simple_html_dom.php';

		$url_get = '/?sortby=price&sortdir=asc&f_price[from]=15&f_price[to]=12000&page=' . $this->page;

		$html = file_get_html($this->url . $url_get);

		$products = $html->find('#products .shk-item');
		$this->products = array();

		foreach($products as $i => $product){
			$pr_title = $product->find('.texture-name-en',0)->plaintext;
			$this->products[$i]['title'] = $pr_title;

			$price = $product->find('.price',0);
			$price = $this->trim_tag('span', $price->innertext);

			$link_product = $this->url . $product->first_child()->action;
			
			$get_product_html = file_get_html($link_product);
			$get_product = $get_product_html->find('.content',0);

			/***** CATEGORIES *****/
			$categories_block_fortab = $get_product->find('.fortab',0);
			$categories_block_row = $categories_block_fortab->children(1);
			$categories_block_links = $categories_block_row->find('a');

			if(count($categories_block_links)){
				$li = 0;
				foreach($categories_block_links as $links){
					if(isset($links->href) && $links->href != '/'){
						$this->products[$i]['categories'][$li] = $links->plaintext;
						$li++;
					}
				}
			}
			
			/***** IMAGES *****/
			$img_src = $get_product->find('.show-prew-box img',0)->src;
			$img_pinfo = pathinfo($img_src);
			$images = WP_CONTENT_URL . '/uploads/'.$this->translit($img_pinfo['filename']).'.'.$img_pinfo['extension'];
			
			$this->products[$i]['full_image']['url'] = $this->url . '/' . $img_src;
			$this->products[$i]['full_image']['path'] = $images;
			$this->products[$i]['full_image']['re_name'] = $this->translit($img_pinfo['filename']).'.'.$img_pinfo['extension'];

			/***** FIELDS *****/
			$this->products[$i]['fields'] = array();

			$this->products[$i]['fields']['tcpc_fields_regular_price'] = $price;
			$this->products[$i]['fields']['tcpc_fields_sale_price'] = '';

			$texture_name = $product->find('.texture-name-ru',0);
			if($texture_name->plaintext != ''){
				$this->products[$i]['fields']['texture_name'] = $texture_name->plaintext;
			}

			$full_name = $get_product->find('.goodorer-h-main',0);
			if($full_name->plaintext != ''){
				$this->products[$i]['fields']['full_name'] = $full_name->plaintext;
			}

			/***** FIELDS -> CHARACTERISTICS *****/
			$char = $get_product->find('.tab',1);
			#$this->pre_print($pr_title);

			if(!isset($char)){
				$char = $get_product_html->find('.tab',1);
			}

			foreach($char->find('tr') as $tr){
				$tr_key = $this->replace_name_key($tr->children(0)->plaintext);
				$field_value_trim = trim($tr->children(1)->plaintext);

				switch ($tr_key) {
					case 'moisture_proof_impregnation':
					case 'presence_substrate':
					case 'underfloor_heating':
					case 'presence_bands':
					case 'floor_wall':
						switch($field_value_trim){
							case 'Да':
							case 'Напольная':
							case 'с подложкой':
								$field_value = 'on';
							break;
							case 'Нет':
							case 'Настенная':
							case 'без подложки':
								$field_value = 'out';
							break;
							default :
								$field_value = 'no';
							break;
						}				
					break;
					default:
						$field_value = $field_value_trim;
					break;
				}

				$this->products[$i]['fields'][$tr_key] = $field_value;
			}
		}
	}

	private function trim_tag($tag, $str){
		return preg_replace("'<".$tag."[^>]*?>.*?</".$tag.">'si","",$str);
	}

	private function replace_name_key($key){
		$key = str_replace(':', '', $key);

		$keys = array(
			'Вес уп.' => 'ves_up',
			'Вес м2' => 'ves_m2',
			'Размер' => 'size',
			'Тип' => 'type',
			'Производитель' => 'manufacturer',
			'Коллекция' => 'collection',
			'Страна производства' => 'country_of_production',
			'Толщина верхнего слоя' => 'thickness',
			'Наличие фаски' => 'chamfer',
			'Тип соединения' => 'connection',
			'Покрытие' => 'coating',
			'Тип поверхности' => 'type_surface',
			'Класс нагрузки' => 'class_load',
			'Влагостойкая пропитка' => 'moisture_proof_impregnation',
			'Наличие подложки' => 'presence_substrate',
			'Оттенок' => 'shade',
			'м2 в упаковке' => 'm2_package',
			'Досок в упаковке' => 'board_package',
			'Номер по каталогу' => 'catalog_number',
			'Порода дерева' => 'tree_species',
			'Тип рисунка' => 'picture_type',
			'Подходит для теплого пола' => 'underfloor_heating',
			'Селекция' => 'selection',
			'Твердость по Бринеллю' => 'brinell_hardness',
			'Поверхность' => 'surface',
			'Термообработка древесины' => 'heat_treatment',
			'Наличие полос' => 'presence_bands',
			'Вид обработки' => 'type_treatment',
			'Напольная/настенная' => 'floor_wall',
		);

		return $keys[$key];
	}

	private function download_file($url, $path){
		$ReadFile = fopen ($url, "rb");
	    
	    if ($ReadFile) {
	        $WriteFile = fopen ($path, "wb");
	        if ($WriteFile){
	            while(!feof($ReadFile)) {
	                fwrite($WriteFile, fread($ReadFile, 4096 ));
	            }
	            fclose($WriteFile);
	        }
	        fclose($ReadFile);
	    }
	}

	private function translit($s){
		$s = (string) $s;
		$s = strip_tags($s);
		$s = str_replace(array("\n", "\r"), " ", $s);
		$s = preg_replace("/\s+/", ' ', $s);
		$s = trim($s);
		$s = function_exists('mb_strtolower') ? mb_strtolower($s) : strtolower($s);
		$s = strtr($s, array('а'=>'a','б'=>'b','в'=>'v','г'=>'g','д'=>'d','е'=>'e','ё'=>'e','ж'=>'j','з'=>'z','и'=>'i','й'=>'y','к'=>'k','л'=>'l','м'=>'m','н'=>'n','о'=>'o','п'=>'p','р'=>'r','с'=>'s','т'=>'t','у'=>'u','ф'=>'f','х'=>'h','ц'=>'c','ч'=>'ch','ш'=>'sh','щ'=>'shch','ы'=>'y','э'=>'e','ю'=>'yu','я'=>'ya','ъ'=>'','ь'=>''));
		$s = preg_replace("/[^0-9a-z-_ ]/i", "", $s);
		$s = str_replace(" ", "-", $s);
			
		return $s;
	}

	private function get_category($category = array()){
		$ids = array();

		foreach($category as $key => $value){
			$term = get_term_by('name', $value, 'tcpc_category');
			if(!empty($term)){
				$ids[] = $term->term_id;
			} else {
				$my_cat = array(
					'cat_name' => $value,
					'category_nicename' => $this->translit($value),
					'taxonomy' => 'tcpc_category',
				);

				if($key != 0){
					$parent_id = get_term_by('name', $category[($key - 1)], 'tcpc_category');
					$my_cat['category_parent'] = $parent_id->term_id;
				}

				$ids[] = wp_insert_category($my_cat);
			}
		}

		return $ids;
	}

	private function add_images($full_image, $post_id, $title = null){
		#$this->download_file($full_image['url'], $full_image['path']);

		if($full_image['re_name'] == 'noimage_556x395_a70' && file_exists($full_image['path'])) return false;

		$file_array = array();
		$tmp = download_url($full_image['url']);
			
		$file_array['name'] = $full_image['re_name'];
		$file_array['tmp_name'] = $tmp;

		$img = media_handle_sideload($file_array, $post_id, $title);

		@unlink($file_array['tmp_name']);

		return $img;
	}

	private function add_post($product){
		$post_title = wp_strip_all_tags($product['title']);
		
		$query = new WP_Query;
		$q = $query->query(array('name'=>$post_title, 'post_type'=>'tcpc'));
		
		if(!empty($q)) return false;

		$category_ids = $this->get_category($product['categories']);

		$post_data = array(
			'post_title'    => $post_title,
			'post_status'   => 'publish',
			'post_type'		=> 'tcpc',
			'post_author'   => 1
		);

		$post_id = wp_insert_post($post_data);

		if(count($product['full_image']) > 0){
			$img = $this->add_images($product['full_image'], $post_id, $post_data['post_title']);

			if($img) set_post_thumbnail($post_id, $img);
		}

		/* FIELDS */
		foreach($product['fields'] as $key => $value){
			add_post_meta($post_id, $key, $value);
		}

		wp_set_object_terms($post_id, 3, 'tcpc_catalog');
		wp_set_object_terms($post_id, $category_ids, 'tcpc_category');

		return [
			'post_id' => $post_id,
			'post_img_id' => $img,
			'category' => $category_ids
		];
	}

	public function parsing(){
		$this->set_products();

		$this->result = array();

		foreach($this->products as $product){
			$this->result[] = $this->add_post($product);
		}

		$this->result['success'] = 'Парсинг выполнен!';
	}

	public function get_result(){
		return $this->result;
	}
}
?>
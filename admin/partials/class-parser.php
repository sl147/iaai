<?php
/**
 * 
 */
class Parser
{

	public function __construct($fileLots)
	{
		# ім'я файлу з лотами
        $this->fileLots = $fileLots;

        # К-ть сторінок перегляду для пошуку лота
        $this->countPage = 3;

        $this->makeVehicle  = '';
        $this->modelVehicle = '';
	}

	public function run_Parser() {
		$this->getTax();
		exit;
		include 'phpQuery-onefile.php';
		$url = 'https://abetter.bid/ru/car-finder/type-automobiles';
		$lines = file($this->fileLots);
		foreach ($lines as $line_num => $lot) {
			if (strlen(trim($lot)) == 8) {
				$start = 0;
				$isL = $this->parser($url, $lot, $start);
				echo "<h2>".$this->makeVehicle." ".$this->modelVehicle." лот:".$lot;
				echo ($isL) ? " оброблено" : " торги завершено.Лот не записано";
				echo "</h2>";				
			}
			else {
				echo "<h2 style='color:red;'>лот:".$lot." не вірний номер лоту.Лот не записано</h2>";
			}
		}
	}

	private function getTax() {
		$post_id_7 = get_post( 3562  );
		//$post_id_7 = get_post( 2723 );
		$title = $post_id_7->post_title;
		echo "post:$title";
//$metas = get_post_meta( $post_id_7->ID, 'images' );
		$metas = get_post_meta( $post_id_7->ID);
echo '<pre>'; //html тег для более наглядного вывода ( вместо \n в каждой строке)
//print_r(get_object_vars($metas));// print_r выводит массив

	print_r($metas);

		if ( function_exists( 'add_theme_support' ) ) {
			echo " exist ";
    			add_theme_support( 'post-thumbnails' ); // @todo check is it needed?
     		}
     		else {
     			echo " nooooo exist ";
     		}

if ( has_post_thumbnail($post_id_7)) { 
echo "issssssss post thumbnail for post:".$post_id_7->ID;
//echo get_the_post_thumbnail($post_id_7->ID, 'thumbnail' );

			echo get_the_post_thumbnail( $post_id_7->ID, 'thumbnail'); ?>
			<h1><?php echo get_the_title($post_id_7); ?></h1>
			<?php echo get_the_excerpt($post_id_7);

	?>

   <a href="<?php the_permalink(); ?>" title="<?php the_title_attribute(); ?>" >
   <?php the_post_thumbnail(); ?>
   </a>
 <?php } 
 else {
 	echo "no post thumbnail for post:".$post_id_7->ID;
 }


    if ($autoshowroom_images = get_field('images',$post_id_7->ID)) {
        foreach ($autoshowroom_images as $image) { ?>
            <li data-src="<?php echo esc_url($image['url']); ?>">
                <a><img src="<?php echo esc_url($image['url']); ?>" alt="<?php echo esc_attr($image['title']); ?>"/></a>
            </li>
        <?php }
    }

	}

	private function parser($url, $lot, $start) {
		$page   = $this->getCurl($url);
		$doc    = phpQuery::newDocument($page);

		$this->getPhoto($href);
		exit;
		$hentry = $doc->find('.carlist');
		$isLot  = false;
		foreach ($hentry as $el) {
			$elem_pq = pq($el); 
			$hr      = $elem_pq->find('a');
			$href    = $hr->attr('href');
			//$this->getPhoto($href);
			if(strpos($href, trim($lot))) {
				//$this->getPhoto($href);
				$propss = $this->getUrl($href);
/*				foreach ($propss as $value) {
					echo $value['name'].': '.$value['val'] . "<br>";
				}*/
				
				if ($this->saveLot($propss)) {
					$isLot = true;
					break;					
				}
				else {
					echo "somethihg wrong";
				}
			}
		}
		if (!$isLot) {
			$nextpage = $doc->find(".pagination .active")->next()->find('a')->attr("href");
			if (!empty($nextpage)) {
				$next = $nextpage;
				$start++;				
				if ($start < $this->countPage) {
					$this->parser($next, $lot, $start);	
				}				
			}
		}
		return $isLot;
	}

	private function getPhoto($href) {
		echo "here";



		//$url        ="https://abetter.bid".$href;
$url        ="https://www.autobidmaster.com/en/carfinder-online-auto-auctions/lot/51070708/COPART_2010_SUBARU_IMPREZA_2_CERT_OF_TITLE-SALVAGE_HARTFORD_CT/";
		$doc        = $this->getCurl($url);
		$details    = $doc->find('.large-images img');
		$i=0;
		foreach ($details as $el) {
			$i++;
			$elem_pq = pq($el);
			$hr      = $elem_pq->find('img');
			$src    = $elem_pq->attr('src');
			$img = file_get_contents($src);
			$overrides = [ 'test_form' => false ];

			// когда мы во фронте
			require_once ABSPATH . 'wp-admin/includes/media.php';
			require_once ABSPATH . 'wp-admin/includes/file.php';
			require_once ABSPATH . 'wp-admin/includes/image.php';

			//$url = 'http://s.w.org/style/images/wp-header-logo.png';
			//$post_id = 3061;
			$post_id = 3563;
			$desc = "Логотип WordPress";

			$img_tag = media_sideload_image( $src, $post_id, $desc );

			if( is_wp_error($img_tag) ){
				echo $img_tag->get_error_message();
			}
			else {
				// добавлено 
				echo "  img_tag:$img_tag<br>";
			}			
			//$movefile  = wp_handle_upload( $img, $overrides );
			//$movefile  = wp_handle_upload( file_put_contents('slimg'.$i.'.jpg', $img), $overrides );
			//$k=file_put_contents('slimg'.$i.'.jpg', $img);
			//echo "k=$k  $i src:$src<br>";
			echo "$i src:$src<br>";
		}
//echo '<pre>'; //html тег для более наглядного вывода ( вместо \n в каждой строке)
//print_r(get_object_vars($details));// print_r выводит массив

//	print_r($car_dealer);		
	}

	private function getUrl($href) {
		$properties = [];
		$url        ="https://abetter.bid".$href;
		$doc        = $this->getCurl($url);
		$details    = $doc->find('.detailstable');
		$d          = 1;
		foreach ($details as $det) {
			if ($d == 3) {
				$elem_pq = pq($det); 
				$trs     = $elem_pq->find('tr');
				$i++;		
				foreach ($trs as $tr) {
					if ($i == 1) {
						$cont = pq($tr);
						$name = $cont->find("td:eq(0)")->text();
						$val  = $cont->find("td:eq(1)")->text();
//echo "3 entry  name:$name    val=$val<br>";
						$new_item = [
							'name' => trim($name),
							'val'  => trim($val),
						];
						array_push($properties, $new_item);
					}
				}				
			}
			$d++;
		}

		$idYear = $doc->find('#lot-title')->text();
		$year = intval(explode(" ",$idYear)[1]);
		$new_item = [
			'name' => trim('sl_year'),
			'val'  => trim($year),
		];
		array_push($properties, $new_item);
		$hentry = $doc->find('.vehicle-properties');
		if ($hentry) {
			$i = 0;			
			foreach ($hentry as $el) {
				$elem_pq = pq($el); 
				$trs = $elem_pq->find('tr');
				$i++;		
				foreach ($trs as $tr) {
					if ($i == 1) {
						$cont = pq($tr);
						$name = $cont->find("td:eq(0)")->text();
						$val  = $cont->find("td:eq(1)")->text();
						$new_item = [
							'name' => trim($name),
							'val'  => trim($val),
						];
						array_push($properties, $new_item);
					}
				}
			}
			return $properties;
		}
		else {
			echo "no hentry";
		}	
	}

	private function getCurl($url) {
		$curl = curl_init($url);
		curl_setopt ($curl, CURLOPT_RETURNTRANSFER, 1);
		curl_setopt ($curl, CURLOPT_SSL_VERIFYPEER, 0);
		curl_setopt ($curl, CURLOPT_SSL_VERIFYHOST, 0);
		curl_setopt ($curl, CURLOPT_FOLLOWLOCATION, 1); //следование 302 redirect 
		$page = curl_exec($curl);
		$doc = phpQuery::newDocument($page);
		return $doc;
	}

	private function saveLot($pars) {
	$metaInput = [];
		foreach ($pars as $pr) {
			if ($pr['name'] == 'Тип документа') {
				$new_item = [
					'd0b4d0bed0bad183d0bcd0b5d0bdd182' => $pr['val'],
				];
				array_push($metaInput, $new_item);
				$type_doc = $pr['val'];
			}
			elseif ($pr['name'] == 'sl_year') {
				$new_item = [
					'registration' => $pr['val'],
				];
				array_push($metaInput, $new_item);			
				$sl_year = $pr['val'];
			}
			elseif ($pr['name'] == 'Место Аукциона') {
				$l = trim(explode("Показать",$pr['val'])[0]);
				$new_item = [
					'd0bfd180d0bed0b1d0b5d0b3-d0bcd0b8d0bbd18c' => $l,
				];
				array_push($metaInput, $new_item);			
				$location = $l;
			}
			elseif ($pr['name'] == 'Sale Type') {
				$new_item = [
					'd182d0b8d0bf-d0bfd180d0bed0b4d0b0d0b6d0b8' => $pr['val'],
				];
				array_push($metaInput, $new_item);			
				$sale_Type = $pr['val'];
			}
			elseif ($pr['name'] == 'Начальная ставка') {
				//echo "start_bid=".$pr['val']."<br>";
				$start_bid = explode("$",$pr['val'])[1];
				$start_bid = explode(" ",$start_bid)[0];
				$c = intval(explode(",",$start_bid)[0]);
				$d = intval(explode(",",$start_bid)[1])/1000;
				$start_bid = $c + $d;
				//echo "start_bid=$start_bid<br>";
				$new_item = [
					'd182d0b5d183d0bad183d189d0b0d18f-d181d182d0b0d0b2d0bad0b0' => $start_bid,
				];
				array_push($metaInput, $new_item);
			}
			elseif ($pr['name'] == 'Одометр') {
				$new_item = [
					'd0bfd180d0bed0b1d0b5d0b3-d0bcd0b8d0bbd18c' => $pr['val'],
				];
				array_push($metaInput, $new_item);			
				$odom = $pr['val'];
			}
			elseif ($pr['name'] == 'Transmission') {
				$new_item = [
					'd182d180d0b0d0bdd181d0bcd0b8d181d181d0b8d18f'=>$pr['val'], //Трансмиссия
				];
				array_push($metaInput, $new_item);			
				$trans = $pr['val'];
			}
			elseif ($pr['name'] == 'Первичное повреждение') {
				$new_item = [
					'd0bed181d0bdd0bed0b2d0bdd0bed0b5-d0bfd0bed0b2d180d0b5d0b6d0b4d0b5d0bdd0b8d0b5'=>$pr['val'],
				];
				array_push($metaInput, $new_item);			
				$primary_damage = $pr['val'];
			}
			elseif ($pr['name'] == 'Вторичное повреждение') {
				$new_item = [
					'd0b4d0bed0bfd0bed0bbd0bdd0b8d182d0b5d0bbd18cd0bdd0bed0b5-d0bfd0bed0b2d180d0b5d0b6d0b4d0b5d0bdd0b8d0b5'=>$pr['val'],
				];
				array_push($metaInput, $new_item);			
				$secondary_damage = $pr['val'];
			}
			elseif ($pr['name'] == 'VIN') {
				$new_item = [
					'vin'=>$pr['val'], //
				];
				array_push($metaInput, $new_item);
				$vin = explode(" ",$pr['val'])[0];
			}
			elseif ($pr['name'] == 'Цвет') {
				$new_item = [
					'd186d0b2d0b5d182'=>$pr['val'], //
				];
				array_push($metaInput, $new_item);			
				$colour = $pr['val'];
			}
			elseif ($pr['name'] == 'Тип кузова') {
				$new_item = [
					'd182d0b8d0bf-d0bad183d0b7d0bed0b2d0b0'=>$pr['val'],
				];
				array_push($metaInput, $new_item);			
				$body_type = $pr['val'];
			}
			elseif ($pr['name'] == 'Тип двигателя') {
				$new_item = [
					'd182d0b8d0bf-d0b4d0b2d0b8d0b3d0b0d182d0b5d0bbd18f'=>$pr['val'],
				];
				array_push($metaInput, $new_item);			
				$engine_type = trim($pr['val']);
			}
			elseif ($pr['name'] == 'Управление') {
				$new_item = [
					'd0bfd180d0b8d0b2d0bed0b4'=>$pr['val'], //
				];
				array_push($metaInput, $new_item);			
				$privod = $pr['val'];
			}
			elseif ($pr['name'] == 'Цилиндр') {
				$new_item = [
					'd186d0b8d0bbd0b8d0bdd0b4d180d0bed0b2'=>$pr['val'], //
				];
				array_push($metaInput, $new_item);			
				$cyl = $pr['val'];
			}
			elseif ($pr['name'] == 'Топливо') {
				$new_item = [
					'd182d0bed0bfd0bbd0b8d0b2d0be'=>$pr['val'], //
				];
				array_push($metaInput, $new_item);			
				$gas = $pr['val'];
			}
			elseif ($pr['name'] == 'Лот #') {
				$new_item = [
					'd0bbd0bed182-d0bdd0bed0bcd0b5d180'=>$pr['val'], //
				];
				array_push($metaInput, $new_item);			
				$NomLot = $pr['val'];
			}
			elseif ($pr['name'] == 'Ключи') {
				$new_item = [
					'd0bad0bbd18ed187d0b8'=>$pr['val'], //
				];
				array_push($metaInput, $new_item);			
				$keys = $pr['val'];
			}
			elseif ($pr['name'] == 'Марка') {
				$make = $this->saveTax('make', $pr['val']);
				$mark = $pr['val'];
				$this->makeVehicle = $pr['val'];
			}
			elseif ($pr['name'] == 'Модель') {
				$mod = $this->saveTax('model', $pr['val']);
				$model = $pr['val'];
				$this->modelVehicle = $pr['val'];
			}	
		}
$meta_input1    = array(
				'd0b4d0bed0bad183d0bcd0b5d0bdd182' => $type_doc,
				'd182d180d0b0d0bdd181d0bcd0b8d181d181d0b8d18f'=>$trans,
				'd0bed181d0bdd0bed0b2d0bdd0bed0b5-d0bfd0bed0b2d180d0b5d0b6d0b4d0b5d0bdd0b8d0b5'=>$primary_damage,
				'd0b4d0bed0bfd0bed0bbd0bdd0b8d182d0b5d0bbd18cd0bdd0bed0b5-d0bfd0bed0b2d180d0b5d0b6d0b4d0b5d0bdd0b8d0b5'=>$secondary_damage,				
				'd182d0b8d0bf-d0bad183d0b7d0bed0b2d0b0'=>$body_type,
				'd186d0b2d0b5d182'=>$colour,
				'd0bfd180d0b8d0b2d0bed0b4'=>$privod,
				'd186d0b8d0bbd0b8d0bdd0b4d180d0bed0b2'=>$cyl,
				'd182d0bed0bfd0bbd0b8d0b2d0be'=>$gas,
				'vin'=>$vin,
				'registration'=>$sl_year,
				'milage'=>$odom,
				'd0b4d0b2d0b8d0b3d0b0d182d0b5d0bbd18c'=>$engine_type,
				'd0bed181d0bd-d0bfd0bed0b2d180d0b5d0b6'=>$primary_damage,
				'd0bbd0bed182-d0bdd0bed0bcd0b5d180'=>$NomLot,
				'd0bad0bbd18ed187d0b8'=>$keys,
				'd180d0b0d181d0bfd0bed0bbd0bed0b6d0b5d0bdd0b8d0b5'=>$location,
				'd182d0b8d0bf-d0bfd180d0bed0b4d0b0d0b6d0b8'=>$sale_Type,
				'd182d0b5d183d0bad183d189d0b0d18f-d181d182d0b0d0b2d0bad0b0'=>$start_bid,
				'pricetext'=>$start_bid,
			);		
		//print_r($metaInput);
//echo '<pre>'; //html тег для более наглядного вывода ( вместо \n в каждой строке)
//print_r(get_object_vars($metaInput));// print_r выводит массив		
		$post_data = array(
			'post_type'     => 'vehicle',
			'post_title'    => $model.' '.$mark,
			'post_content'  => 'post_content',
			'post_status'   => 'publish',
			'post_author'   => 1,
			'tax_input'     => array( 'vehicle_type' => array( $body_type ), 'model' => array( $model ), 'make' => array( $mark )  ),
			'meta_input'    => $meta_input1,
/*			'meta_input'    => array(
				'd0b4d0bed0bad183d0bcd0b5d0bdd182' => $type_doc,
				'd182d180d0b0d0bdd181d0bcd0b8d181d181d0b8d18f'=>$trans,
				'd0bed181d0bdd0bed0b2d0bdd0bed0b5-d0bfd0bed0b2d180d0b5d0b6d0b4d0b5d0bdd0b8d0b5'=>$primary_damage,
				'd0b4d0bed0bfd0bed0bbd0bdd0b8d182d0b5d0bbd18cd0bdd0bed0b5-d0bfd0bed0b2d180d0b5d0b6d0b4d0b5d0bdd0b8d0b5'=>$secondary_damage,				
				'd182d0b8d0bf-d0bad183d0b7d0bed0b2d0b0'=>$body_type,
				'd186d0b2d0b5d182'=>$colour,
				'd0bfd180d0b8d0b2d0bed0b4'=>$privod,
				'd186d0b8d0bbd0b8d0bdd0b4d180d0bed0b2'=>$cyl,
				'd182d0bed0bfd0bbd0b8d0b2d0be'=>$gas,
				'vin'=>$vin,
				'registration'=>$sl_year,
				'milage'=>$odom,
				'd0b4d0b2d0b8d0b3d0b0d182d0b5d0bbd18c'=>$engine_type,
				'd0bed181d0bd-d0bfd0bed0b2d180d0b5d0b6'=>$primary_damage,
				'd0bbd0bed182-d0bdd0bed0bcd0b5d180'=>$NomLot,
				'd0bad0bbd18ed187d0b8'=>$keys,
				'd180d0b0d181d0bfd0bed0bbd0bed0b6d0b5d0bdd0b8d0b5'=>$location,
				'd182d0b8d0bf-d0bfd180d0bed0b4d0b0d0b6d0b8'=>$sale_Type,
				'd182d0b5d183d0bad183d189d0b0d18f-d181d182d0b0d0b2d0bad0b0'=>$start_bid,
				'pricetext'=>$start_bid,
			),*/
			'post_category' => array(8,39)
		);

		// Вставляем дані в БД
		return wp_insert_post( wp_slash($post_data, $wp_error=true) );
	}


	private function saveTax($taxonomy, $val) {
		$term = term_exists( $val, $taxonomy ); 
		if ($term) {
			return  $term['term_taxonomy_id'];		
		}
		$tax = wp_insert_term($val,$taxonomy,  array(
			'description' => '',
			'parent'      => 0,
			'slug'        => $val,
		) );
		$term = term_exists( $val, $taxonomy );
		if ($term) {
			echo "<h3>нова ";
			if ($taxonomy == 'make') {
				echo "марка ".$val;	
			}
			elseif ($taxonomy == 'model') {
				echo "модель ".$val;	
			}
			echo " записано</h3>";			
			return  $term['term_taxonomy_id'];
		}		
	}
}
?>
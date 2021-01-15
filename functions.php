<?php

add_theme_support('post-thumbnails');

/**
 * Never worry about cache again!
 */
function my_load_scripts($hook) {
	wp_enqueue_style( 'my_css',    get_template_directory_uri() . '/css/style.css', false,  filemtime(get_template_directory() . '/css/style.css') );
	wp_enqueue_style( 'fonts_css',    get_template_directory_uri() . '/fonts/icomoon/icon-font.css', false );
	wp_enqueue_style( 'animate_css',    get_template_directory_uri() . '/libs/animate/animate.min.css', false );

    wp_deregister_script('jquery');
    //wp_enqueue_script( 'jquery', 'https://code.jquery.com/jquery-2.2.4.min.js', array(), null, true );
    wp_enqueue_script( 'jquery', 'https://code.jquery.com/jquery-3.5.1.min.js', array(), null, true );
    wp_enqueue_script( 'ymaps_js', 'https://api-maps.yandex.ru/2.1/?apikey=f7f5866c-fcab-4da8-94d7-cdbdb39c7d22&lang=ru_RU', array('jquery') );
    wp_enqueue_script( 'popper_js', get_template_directory_uri() . '/libs/bootstrap/js/popper.min.js', array('jquery'), null, true );
    wp_enqueue_script( 'bootstrap_js', get_template_directory_uri() . '/libs/bootstrap/js/bootstrap.min.js', array('jquery'), null, true );
    wp_enqueue_script( 'ofi_js', get_template_directory_uri() . '/libs/ofi/ofi.min.js', array('jquery'), null, true );
    wp_enqueue_script( 'wow_js', get_template_directory_uri() . '/libs/wowjs/wow.min.js', array('jquery'), null, true );
    wp_enqueue_script( 'custom_js', get_template_directory_uri() . '/js/scripts.js', array(
    	'jquery',
	    'popper_js',
	    'bootstrap_js',
	    'ofi_js',
	    'wow_js',
    ), filemtime(get_template_directory() . '/js/scripts.js'), true );
	$backend_data = [
		'ajax_url' => admin_url( 'admin-ajax.php' ),
		'theme_url' => get_template_directory_uri(),
	];
	wp_localize_script('custom_js','backend_data',$backend_data);
}
add_action('wp_enqueue_scripts', 'my_load_scripts');

add_action( 'init', 'register_cpt' );
function register_cpt() {
    $labels = array(
        'name' => 'Объекты недвижимости',
        'singular_name' => 'Объект недвижимости',
    );
    $supports = array('title', 'editor', 'thumbnail');
    register_post_type( 'property',
        array(
            'labels' => $labels,
            'public' => true,
            'supports' => $supports,
            'show_in_rest' => true,
        )
    );
    register_taxonomy('property-class','property',[
	    'labels' => [
		    'name' => 'Классы жилья',
		    'singular_name' => 'Класс жилья',
	    ],
	    'show_in_rest' => true,
    ]);
}

if( function_exists('acf_add_options_page') ) {
	acf_add_options_page(array(
		'page_title' 	=> 'Глобальные настройки',
		'menu_title'	=> 'Глобальные настройки',
		'menu_slug' 	=> 'theme-general-settings',
		'capability'	=> 'edit_posts',
		'redirect'		=> false
	));
}

function acf_load_metro_stations_line_field_choices( $field ) {
	$field['choices'] = array();
	if( have_rows('metro_lines', 'option') ) {
		while( have_rows('metro_lines', 'option') ) {
			the_row();
			$value = get_sub_field('metro_lines_name');
			$label = get_sub_field('metro_lines_name');
			$field['choices'][ $value ] = $label;
		}
	}
	return $field;
}
add_filter('acf/load_field/name=metro_stations_line', 'acf_load_metro_stations_line_field_choices');

function acf_load_metro_distances_station_field_choices( $field ) {
	$field['choices'] = array();
	if( have_rows('metro_stations', 'option') ) {
		while( have_rows('metro_stations', 'option') ) {
			the_row();
			$value = get_sub_field('metro_stations_name');
			$label = get_sub_field('metro_stations_name');
			$field['choices'][ $value ] = $label;
		}
	}
	return $field;
}
add_filter('acf/load_field/name=metro_distances_station', 'acf_load_metro_distances_station_field_choices');

$metro_lines = [];
foreach(get_field('metro_lines','option') as $metro_line){
	$metro_lines[$metro_line['metro_lines_name']] = $metro_line['metro_lines_icon'];
};
$metro_stations = [];
foreach(get_field('metro_stations','option') as $metro_station){
	$metro_stations[$metro_station['metro_stations_name']] = $metro_station['metro_stations_line'];
};

add_action( 'wp_ajax_filter_action', 'filter_action' );
add_action( 'wp_ajax_nopriv_filter_action', 'filter_action' );
function filter_action() {
    global $metro_lines;
    global $metro_stations;
	$args = [
		'post_type'      => 'property',
		'posts_per_page' => 6,
        'paged'          => ($_POST['page']) ? $_POST['page'] : 1,
	];

	$proximity = $_POST['proximity'];
	if ( !empty( $proximity ) && count( $proximity ) < 4 ) {
	    // среди параметров фильтра пришли значения минимального расстояния до метро
	    // причём не все возможные
			$proximity_meta = [
				'relation' => 'OR',
            ];
			if ( in_array( 'less10', $proximity) ) {
				$proximity_meta[] = [
					'key'     => 'min_proximity',
					'value'   => 10,
					'compare' => '<',
                    'type'    => 'NUMERIC',
				];
			} elseif ( in_array( '10-20', $proximity) ) {
				$proximity_meta[] = [
					'key'     => 'min_proximity',
					'value'   => [10,20],
					'compare' => 'BETWEEN',
					'type'    => 'NUMERIC',
				];
			} elseif ( in_array( '20-40', $proximity) ) {
				$proximity_meta[] = [
					'key'     => 'min_proximity',
					'value'   => [20,40],
					'compare' => 'BETWEEN',
					'type'    => 'NUMERIC',
				];
			} elseif ( in_array( 'more40', $proximity) ) {
				$proximity_meta[] = [
					'key'     => 'min_proximity',
					'value'   => 40,
					'compare' => '>',
					'type'    => 'NUMERIC',
				];
			}
	} else { // среди параметров фильтра не пришли значения минимального расстояния до метро ИЛИ пришли все значения фильтра
	         // значит искать объекты с любыми значения
		$proximity_meta = array(
			array(
				'key'     => 'metro_distances_$_metro_distances_station',
				'compare' => 'EXISTS',
			),
		);
    }

	$property_classes = $_POST['class'];
    if ( ! empty( $property_classes ) ) {
	    $property_classes_meta = [
		    [
			    'taxonomy' => 'property-class',
			    'field'    => 'slug',
			    'terms'    => $property_classes,
		    ]
	    ];
    }

	$args['meta_query'] = [
		'relation' => 'AND',
		$proximity_meta,
	];
	if ( ! empty( $property_classes_meta ) ) {
		$args['tax_query'] = $property_classes_meta;
	}
	$likes = json_decode( $_COOKIE['likes'] );
	$the_query = new WP_Query( $args ); ?>

	<?php if ( $the_query->have_posts() ) : ?>

		<?php while ( $the_query->have_posts() ) : $the_query->the_post(); ?>
            <li class="page-loop__item wow animate__animated animate__fadeInUp"
                data-property_id="<?=get_the_ID();?>"
                data-wow-duration="0.8s">

				<a href="#" class="favorites-link <?php
				if (is_array($likes) && !empty($likes) && in_array(get_the_ID(),$likes)) {
					echo 'favorites-link__delete';
				} else {
					echo 'favorites-link__add';
				}
				?>" title="Добавить в Избранное"
				   role="button">
					<span class="icon-heart"><span class="path1"></span><span class="path2"></span></span>
				</a>

				<a href="<?php the_permalink(); ?>" class="page-loop__item-link">

					<div class="page-loop__item-image">

						<img src="<?php the_post_thumbnail_url(); ?>" alt="<?php the_title(); ?>">

						<div class="page-loop__item-badges">
							<span class="badge">Услуги 0%</span>

							<?php $property_class = get_the_terms( get_the_ID(), 'property-class' )[0]->name; ?>

							<span class="badge"><?= $property_class; ?></span>
						</div>

					</div>

					<div class="page-loop__item-info">

						<h3 class="page-title-h3"><?php the_title(); ?></h3>

						<p class="page-text">Срок сдачи до <?php the_field( 'delivery_time' ); ?></p>

						<?php $metro_distances = get_field( 'metro_distances' );
						if ( ! empty( $metro_distances ) ):
							$best_station = $metro_distances[0];
							for ( $i = 1; $i <= count( $metro_distances ); $i ++ ) {
								if ( ! empty( $metro_distances[ $i ]['metro_distances_minutes'] ) && intval( $metro_distances[ $i ]['metro_distances_minutes'] ) < intval( $best_station['metro_distances_minutes'] ) ) {
									$best_station = $metro_distances[ $i ];
								}
							} ?>

							<div class="page-text to-metro">
								<span class="icon-metro <?= $metro_lines[ $metro_stations[ $best_station['metro_distances_station'] ] ]; ?>"></span>
								<span class="page-text"><?= $best_station['metro_distances_station']; ?> <span> <?= $best_station['metro_distances_minutes']; ?> мин.</span></span>
								<span class="<?php
									if ( $best_station['metro_distances_type'] == 'Пешком' ) {
										echo 'icon-walk-icon';
									} elseif ( $best_station['metro_distances_type'] == 'Наземный общественный транспорт' ) {
										echo 'icon-bus';
									}
								?>"></span>
							</div>

						<?php endif; ?>

						<span class="page-text text-desc"><?php the_field( 'address' ); ?></span>

					</div>

				</a>

			</li>
		<?php endwhile; ?>

		<?php wp_reset_postdata(); ?>

	<?php else : ?>no_data<?php endif;
	die();
}

function property_where( $where ) {
	$where = str_replace("meta_key = 'metro_distances_$", "meta_key LIKE 'metro_distances_%", $where);
	return $where;
}
add_filter('posts_where', 'property_where');

add_action('acf/save_post', 'post_updated_function');
function post_updated_function($post_ID){
	$min_proximity = '';
	$metro_distances = get_field( 'metro_distances', $post_ID );
	if ( ! empty( $metro_distances ) ) {
		foreach ( $metro_distances as $distance ) {
			if ( ! empty( $distance['metro_distances_minutes'] ) ) {
				if ( empty($min_proximity) || ( !empty($min_proximity) && ($min_proximity > intval( $distance['metro_distances_minutes'])) ) ) {
					$min_proximity = intval( $distance['metro_distances_minutes'] );
				}
			}
		}
	}
	update_field( 'min_proximity', $min_proximity, $post_ID );
}
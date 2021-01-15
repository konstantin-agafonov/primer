<?php
$likes = json_decode( $_COOKIE['likes'] );
?>


<!DOCTYPE html>
<html lang="en">

<head>
	<meta charset="UTF-8" />
	<meta name="viewport" content="width=device-width, initial-scale=1.0" />
	<meta http-equiv="X-UA-Compatible" content="IE=edge" />
	<meta name="robots" content="noindex">
	<title>Metro City | Новостройки</title>
    <?php wp_head(); ?>
</head>

<body>

	<main class="main">

		<div class="container">

			<div class="page-top">

                <nav class="page-breadcrumb" itemprop="breadcrumb">
                    <a href="<?=home_url('/');?>">Главная</a>
                    <span class="breadcrumb-separator"> > </span>
                    <a href="<?=home_url('/');?>">Новостройки</a>
                    <span class="breadcrumb-separator"> > </span>
					<?php the_title(); ?>
                </nav>

			</div>

			<div class="page-section">

				<div class="page-content">

                    <article class="post" data-property_id="<?=get_the_ID();?>">

                        <div class="post-header">

                            <h1 class="page-title-h1"><?php the_title(); ?></h1>

                            <span><?php the_field('company'); ?></span>

                            <div class="post-header__details">

                                <div class="address"><?php the_field('address'); ?></div>

	                            <?php
	                            if ( have_rows( 'metro_distances' ) ):
		                            while ( have_rows( 'metro_distances' ) ) : the_row();
			                            $station = get_sub_field( 'metro_distances_station' );
                                        ?>

                                        <div class="metro">
                                            <span class="icon-metro <?=$metro_lines[$metro_stations[$station]];?>"></span>
	                                        <?=$station ?>
                                            <span>
                                                <?=get_sub_field( 'metro_distances_minutes' );?>
                                                мин.<span class="<?php
                                                    $type = get_sub_field( 'metro_distances_type' );
                                                    if ($type == 'Пешком') {
                                                        echo 'icon-walk-icon';
                                                    } elseif($type == 'Наземный общественный транспорт') {
	                                                    echo 'icon-bus';
                                                    }
                                                ?>"></span>
                                            </span>
                                        </div>

                                        <?php
		                            endwhile;
	                            endif; ?>

                            </div>

                        </div>

                        <div class="post-image">

                            <img src="<?php the_post_thumbnail_url(); ?>" alt="<?php the_title(); ?>">

                            <div class="page-loop__item-badges">
                                <span class="badge">Услуги 0%</span>

	                            <?php $property_class = get_the_terms(get_the_ID(),'property-class')[0]->name;?>

                                <span class="badge"><?=$property_class;?></span>
                            </div>

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

                        </div>

                        <h2 class="page-title-h1">Характеристики ЖК</h2>

                        <ul class="post-specs">
                            <li>
                                <span class="icon-building"></span>
                                <div class="post-specs__info">
                                    <span>Класс жилья</span>
                                    <p><?=$property_class;?></p>
                                </div>
                            </li>
                            <li>
                                <span class="icon-brick"></span>
                                <div class="post-specs__info">
                                    <span>Конструктив</span>
                                    <p>Монолит-кирпич</p>
                                </div>
                            </li>
                            <li>
                                <span class="icon-paint"></span>
                                <div class="post-specs__info">
                                    <span>Отделка</span>
                                    <p>
                                        Чистовая
                                        <span class="tip tip-info" data-toggle="popover" data-placement="top"
                                              data-content="And here's some amazing content. It's very engaging. Right?">
						<span class="icon-prompt"></span>
					</span>
                                    </p>
                                </div>
                            </li>
                            <li>
                                <span class="icon-calendar"></span>
                                <div class="post-specs__info">
                                    <span>Срок сдачи</span>
                                    <p><?php the_field('delivery_time');?></p>
                                </div>
                            </li>
                            <li>
                                <span class="icon-ruller"></span>
                                <div class="post-specs__info">
                                    <span>Высота потолков</span>
                                    <p>2,7 м</p>
                                </div>
                            </li>
                            <li>
                                <span class="icon-parking"></span>
                                <div class="post-specs__info">
                                    <span>Подземный паркинг</span>
                                    <p>Присутствует</p>
                                </div>
                            </li>
                            <li>
                                <span class="icon-stair"></span>
                                <div class="post-specs__info">
                                    <span>Этажность</span>
                                    <p>10-17</p>
                                </div>
                            </li>
                            <li>
                                <span class="icon-wallet"></span>
                                <div class="post-specs__info">
                                    <span>Ценовая группа</span>
                                    <p>Выше среднего</p>
                                </div>
                            </li>
                            <li>
                                <span class="icon-rating"></span>
                                <div class="post-specs__info">
                                    <span>Рейтинг</span>
                                    <p>8.8</p>
                                </div>
                            </li>
                        </ul>

                        <h2 class="page-title-h1">Краткое описание</h2>

                        <div class="post-text">
                            <?php the_content(); ?>
                        </div>

                        <h2 class="page-title-h1">Карта</h2>

                        <div class="post-map" id="post-map" style="width: 100%; height: 300px;"></div>

                    </article>

                </div>

				<div class="page-filter"></div>

			</div>

		</div>

	</main>

    <?php wp_footer(); ?>
</body>

</html>

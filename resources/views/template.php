<!DOCTYPE html>
<html <?php language_attributes(); ?> class="no-js">
<head>
  <?php
  	$theme_options = _WSH()->option();
	$one_page_setting = sh_set(sh_set($theme_options , 'one_page_section') , 'one_page_section');
	$one_page_links = sh_set(sh_set($theme_options , 'one_page_links'), 'one_page_links');
	$news_opacity = sh_set($theme_options , 'header_overlay_opacity')/100;
	echo ( sh_set( $theme_options, 'site_favicon' ) ) ? '<link rel="icon" type="image/png" href="'.sh_set( $theme_options, 'site_favicon' ).'">' : '' ;
	
	$class_of_body = (!(is_home() || is_front_page()) || ($wp_query->is_posts_page)) ? 'blog' : '' ;
	$blog_class = 'blog_class';
	
	if( ( (is_home() || is_front_page())  && !($wp_query->is_posts_page) ) && sh_set($theme_options , 'show_app_presentation_section')):
		$blog_class = '';
	endif;
  ?>
    <meta charset="utf-8">
    <meta http-equiv="X-UA-Compatible" content="IE=edge">
    <meta name="viewport" content="width=device-width, initial-scale=1">
	
	<?php wp_head(); ?>
  </head>
<body <?php body_class($class_of_body); ?>>
	<?php wp_body_open(); ?>
<?php if(sh_set($theme_options , 'cs_preloader')): ?>
<div id="preload">
	<div class="container logo">
		<img src="<?php echo sh_set($theme_options , 'cs_preloader_image'); ?>" class="prl animated fadeInDown delayp4" alt="<?php esc_attr__("Image" , 'wp-rocketapp'); ?>">
		<p class="text-logo animated bounceIn delayp2"><?php echo sh_set($theme_options , 'cs_preloader_text'); ?></p>
	</div>
</div>
<?php endif; ?>
<header id="option_header" style="background-image:url(<?php echo (sh_set($theme_options , 'header_bg_image'))?sh_set($theme_options , 'header_bg_image'):get_template_directory_uri('template_url').'/img/main_top_bg.jpg'; ?>)">

  <div class="black-background-overlay" style="opacity: <?php echo esc_attr($news_opacity); ?>;"></div>
  <div class="container top_interface <?php echo esc_attr($blog_class); ?>">
	
      <div class="logo col-md-2 col-sm-8 col-xs-8">
        <div class="text-logo">
			<?php
				
				$logo_link = (sh_set($theme_options , 'show_one_page_settings') && (is_home() || is_front_page()) && !($wp_query->is_posts_page))?'#option_header':home_url();
				
				$logo_class = (sh_set($theme_options , 'show_one_page_settings') && (is_home() || is_front_page()) && !($wp_query->is_posts_page))?'blogmenu':'';
			?>
				<a class="<?php echo esc_attr($logo_class); ?>" href="<?php echo esc_url($logo_link); ?>">
					<?php if(sh_set($theme_options , 'logo_selection')=='image'): ?>
				
					<img src="<?php echo sh_set($theme_options , 'logo_image'); ?>" alt="<?php esc_attr__("Image" , 'wp-rocketapp'); ?>" width="<?php echo sh_set($theme_options , 'logo_width'); ?>" height="<?php echo sh_set($theme_options , 'logo_height'); ?>" />
					<?php	
                        else:
							echo sh_set($theme_options , 'header_logo_name') ? sh_set($theme_options , 'header_logo_name') : wp_kses( "Rocket<b>App</b>" , ra_expanded_alowed_tags() );
						endif;
					?>
				</a>
		</div>
      </div>

	  <div class="col-md-10 col-sm-4 col-xs-4">
      	<i class="fa fa-bars phone-menu hidden-md hidden-lg"></i>
        
		<?php if(sh_set($theme_options , 'show_one_page_settings')): ?>
        <nav class="main_navigation">
        	<ul>
				<?php  
					global $wp_query;
					
					foreach((array)$one_page_setting as $sec): 
						if(sh_set($sec , 'tocopy')) break;
						if(sh_set($sec , 'menu_name')):
						$post_data = get_post(sh_set($sec,'section_page'));
				?>
				<li>				
					
					<a class="<?php echo ((is_home() || is_front_page()) && !($wp_query->is_posts_page))?'blogmenu':''; ?>" href="<?php echo ((is_home() || is_front_page()) && !($wp_query->is_posts_page))?'#'.$post_data->post_name:home_url( '/' ).'#'.$post_data->post_name; ?>">
						<?php echo sh_set($sec , 'menu_name');?>
					</a>
					
					
				</li>
				<?php 
						endif;
					endforeach ;
					
					if(is_array($one_page_links)):
					foreach((array)$one_page_links as $one_page_link):
						if(sh_set($one_page_link , 'tocopy')) break;
						if(sh_set($one_page_link , 'menu_links_name')):
				?>
				<li>
					<a href="<?php echo sh_set($one_page_link,'menu_link'); ?>">
						<?php echo sh_set($one_page_link , 'menu_links_name');?>
					</a>
				</li>
				
				<?php 
						endif;
					endforeach;
					endif;
				?>
				<li>
					<a href="/privacy-policy">
						Terms & Policy
					</a>
				</li>
			</ul>
		</nav>
		<?php 
			else:
				wp_nav_menu(
						array(  
							'theme_location'=> 'header_menu', 
							'menu_class'=>'' ,
							'menu_id' => '' , 
							'container' =>'nav' , 
							'container_class'=> 'main_navigation',
							'depth' => 1,
						)
				); 
			endif;
		?>
	</div>
	
</div>
<?php if(sh_set($theme_options , 'sticky_nav')): ?>
<div class="container top_interface sticky_menu">
	
      <div class="logo col-md-2 col-sm-8 col-xs-8">
        <div class="text-logo">
			<?php 
				if(sh_set($theme_options , 'logo_selection')=='image' || sh_set($theme_options , 'logo_selection')=='text'):
				$logo_link = (sh_set($theme_options , 'show_one_page_settings') && (is_home() || is_front_page()) && !($wp_query->is_posts_page))?'#option_header':home_url();
				$logo_class = (sh_set($theme_options , 'show_one_page_settings') && (is_home() || is_front_page()) && !($wp_query->is_posts_page))?'blogmenu':'';
			?>
				<a class="<?php echo esc_attr($logo_class); ?>" href="<?php echo esc_url($logo_link); ?>">
					<?php if(sh_set($theme_options , 'logo_selection')=='image'): ?>
				
					<img src="<?php echo sh_set($theme_options , 'logo_image'); ?>" alt="<?php esc_attr__("Image" , 'wp-rocketapp'); ?>" width="<?php echo sh_set($theme_options , 'logo_width'); ?>" height="<?php echo sh_set($theme_options , 'logo_height'); ?>" />
					<?php
						else:
							echo sh_set($theme_options , 'header_logo_name') ? sh_set($theme_options , 'header_logo_name') : wp_kses( "Rocket<b>App</b>" , ra_expanded_alowed_tags() );
						endif;
					?>
				</a>
			<?php endif; ?>
		</div>
      </div>

	  <div class="col-md-10 col-sm-4 col-xs-4">
      	<i class="fa fa-bars phone-menu hidden-md hidden-lg"></i>
		<?php 
			if(!(sh_set($theme_options , 'show_one_page_settings'))):
				wp_nav_menu(
						array(  
							'theme_location'=> 'header_menu', 
							'menu_class'=>'' ,
							'menu_id' => '' , 
							'container' =>'nav' , 
							'container_class'=> 'main_navigation',
							'depth' => 1,
						)
				); 
			endif;
			
			if(sh_set($theme_options , 'show_one_page_settings')):
		?>
        <nav class="main_navigation">
        	<ul>
				<?php  
					global $wp_query;
					
					foreach((array)$one_page_setting as $sec): 
						if(sh_set($sec , 'menu_name')):
						$post_data = get_post(sh_set($sec,'section_page'));
				?>
				<li>
					<a class="<?php echo ((is_home() || is_front_page()) && !($wp_query->is_posts_page))?'blogmenu':''; ?>" href="<?php echo ((is_home() || is_front_page()) && !($wp_query->is_posts_page))?'#'.$post_data->post_name:home_url( '/' ).'#'.$post_data->post_name; ?>">
						<?php echo sh_set($sec , 'menu_name');?>
					</a>
				</li>
				<?php 
						endif;
					endforeach ;
					if(is_array($one_page_links)):
					foreach((array)$one_page_links as $one_page_link):
						if(sh_set($one_page_link , 'tocopy')) break;
						if(sh_set($one_page_link , 'menu_links_name')):
				?>
				<li>
					<a href="<?php echo sh_set($one_page_link,'menu_link'); ?>"><?php echo sh_set($one_page_link , 'menu_links_name');?></a>
				</li>
				
				<?php 
						endif;
					endforeach;
					endif;
				?>
				
			</ul>
		</nav>
		<?php endif; ?>
	</div>
	
</div>
<?php endif; ?>
<?php 
	if((is_home() || is_front_page()) && !($wp_query->is_posts_page)):
	
	$features = sh_set(sh_set($theme_options, 'features') , 'features' );
	$no_of_feature = '';
	if(is_array($features)):
		$no_of_feature = count($features) ;	
	 endif;
	 
	if( ( $no_of_feature > 1 || sh_set($theme_options , 'show_phone_image') ) && sh_set($theme_options , 'show_app_presentation_section')):
	
?>
  <div class="container header_tag" style="position:relative;">
  
        <?php if(sh_set($theme_options , 'feature_app_title')): ?>
        	<h1><?php echo sh_set($theme_options , 'feature_app_title') ; ?></h1>
		<?php endif; ?>
		
        <?php if(sh_set($theme_options , 'feature_app_text')): ?>
        	<h2><?php echo sh_set($theme_options , 'feature_app_text') ; ?></h2>
		<?php endif; ?>
		
		<?php
			$buttons = sh_set(sh_set($theme_options, 'buttons') , 'buttons' );
        	foreach((array)$buttons as $button): 
				if(sh_set($button , 'tocopy') ) break;
				if(sh_set($button , 'button_text')):
		?>
                <a href="<?php echo sh_set($button , 'button_link'); ?>" class="w_btn">
                    <?php echo sh_set($button , 'button_text'); ?>
                </a>
		<?php
				endif;
			endforeach;
		?>
		
  </div>

  <div class="container phone_preview">
  	
	<?php if(!sh_set($theme_options , 'show_phone_image')): ?><div class="col-md-2"></div><?php endif; ?>
  
    <div class="col-md-3 hidden-xs hidden-sm col-sm-4 <?php echo (sh_set($theme_options , 'show_phone_image')) ? 'regular_text_left' : 'regular_text_right_without_phone' ; ?>">
	
	 <?php 
		$count = 1 ;
		foreach((array)$features as $feature): 
			if( isset( $feature['tocopy'] ) ) continue;
			$icon = sh_set($feature , 'feature_icon');
			if(($count%2)!=0):
	 ?>
	  
      <h3><?php echo sh_set($feature , 'feature_title'); ?> <i class="fa <?php echo esc_attr($icon); ?>"></i></h3>
      <p><?php echo sh_set($feature , 'feature_detail'); ?></p>
	  
	 <?php 
	 	endif;
	 	if($no_of_feature == $count) 
			break;
		$count++;
	 	endforeach; 
	 ?>
	  
    </div>  
	
	<?php if(sh_set($theme_options , 'show_phone_image')): ?>
	<div class="col-md-1"></div>
    <div class="col-md-4 col-sm-8 app_phone">
		<?php
			$img_id = sh_get_attachment_id_by_url(sh_set($theme_options , 'app_presentation_image'));
			$img_src = sh_set(wp_get_attachment_image_src( $img_id , 'full') , 0);
			if(sh_set($theme_options , 'video')):
				if( function_exists('ra_video_header') ) ra_video_header();
		?>
		<a class="fancybox-media" href="<?php echo sh_set($theme_options , 'video'); ?>" >
		<?php endif; ?>
        
			<img src="<?php echo esc_url($img_src); ?>" class="img-responsive phone_responsive" alt="<?php esc_attr__("Image" , 'wp-rocketapp'); ?>" />
            
		<?php if(sh_set($theme_options , 'video')): ?></a><?php endif; ?>
        
	</div>
	<div class="col-md-1"></div>
	
	<?php endif; ?>
	
	<?php if(!sh_set($theme_options , 'show_phone_image')): ?><div class="col-md-2"></div><?php endif; ?>
	
    <div class="col-md-3 hidden-xs <?php echo (sh_set($theme_options , 'show_phone_image')) ? 'regular_text_right' : 'regular_text_right_without_phone' ; ?>">
	<?php 
		$count2 = 1 ;
		foreach((array)$features as $feature): 
			if( sh_set($feature , 'tocopy') ) continue;
			$icon = sh_set($feature , 'feature_icon');
			if(($count2%2)==0):
	?>
	  
            <h3><?php echo sh_set($feature , 'feature_title'); ?> <i class="fa <?php echo esc_attr($icon) ; ?>"></i></h3>
            <p><?php echo sh_set($feature , 'feature_detail'); ?></p>
	  
	 <?php 
		 	endif; 
			if($no_of_feature == $count2) break;
			$count2++;
		 endforeach; 
	 ?>
	  
    </div>  
	
  </div>
<?php 
	endif;
	endif;  
?>

</header>
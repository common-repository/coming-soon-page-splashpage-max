<?php $nnmcs = new NNM_Coming_Soon_Layout; ?>
<!DOCTYPE html>
<html <?php language_attributes(); ?> class="no-js">
<head>
	<meta charset="<?php bloginfo( 'charset' ); ?>">
	<meta name="viewport" content="width=device-width, initial-scale=1">
	<link rel="profile" href="http://gmpg.org/xfn/11">
	<link rel="pingback" href="<?php bloginfo( 'pingback_url' ); ?>">
	<?php wp_head(); ?>
</head>
<body <?php body_class(); ?>>
	<header></header>
	<main id="nnm-coming-soon" class="nnmcs-coming-soon-page">
		<div class="nnmcs-wrap">
			<div class="nnmcs-align">
				<?php $nnmcs->get_content_layout_template(); ?>
			</div>
		</div>
	</main>
	<footer></footer>
<?php wp_footer(); ?>
</body>
</html>
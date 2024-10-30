<div class="wrap">
<h2><!--Wordpress notices here --></h2>
 <header>
    <div class="cg-col-left">
        <h2>WP Cache</h2>
        Simple, effective Caching Plugin
</div>

<div class="cg-col-right"><strong>Help us build a better product</strong>
<p><a target="blank" href="#">Rate us on WordPress.org</a></p>
<div class="cg-stars">

</div>
</div>
<div style="clear:both"></div>
    </header>

<div class="cg-container">
<div class="cg-tab-wrap">
   <ul class="tabs">
   <?php $page =  sanitize_text_field($_GET['page']); ?>
  <li> <a class="nav-tab <?php if($page == 'optimisationio')echo 'nav-tab-active';?>" href="?page=optimisationio"> Caching </a></li>
  <li><a class="nav-tab <?php if($page == 'optimisationio-cdn-enabler')echo 'nav-tab-active';?>"  href="admin.php?page=optimisationio-cdn-enabler"> CDN Rewrite </a> </li>
    </ul>

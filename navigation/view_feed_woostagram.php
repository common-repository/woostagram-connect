<?php
namespace AC\Navigation;
defined('ABSPATH') or die("No script kiddies please!");

if(get_option('ac_access_token') != false) {
  add_thickbox();
?>
<div class="wrap">
  <h2>Your Recent Media</h2>
  <input type="hidden" id="next_url">
  <ul id="container"></ul>
  <a href="#TB_inline?width=400&height=150&inlineId=pop-up-blocked" class="thickbox" style="display: none;">show notification</a>
  <button id="load_more" style="display: none;">Load More</button>
  
  <!-- pop up notification -->
  <div id="pop-up-blocked" style="display: none;">
    <div class="media-frame-title"><h1>Success!</h1></div>
    <p>Photo has been imported. You can add price, stock, and any other attributes later on <a href="<?php echo admin_url('edit.php?post_type=product'); ?>" target="_blank">Woocommerce product page.</a></p>
  </div>
  
  <p><i>Psst..if you are happy with this plugin, you can buy me a cup of coffee.</i></p>
  <p>
    <form action="https://www.paypal.com/cgi-bin/webscr" method="post" target="_top">
      <input type="hidden" name="cmd" value="_s-xclick">
      <input type="hidden" name="hosted_button_id" value="V57TSXWEHGPZE">
      <input type="image" src="https://www.paypalobjects.com/en_US/i/btn/btn_donateCC_LG.gif" border="0" name="submit" alt="PayPal - The safer, easier way to pay online!">
      <img alt="" border="0" src="https://www.paypalobjects.com/en_US/i/scr/pixel.gif" width="1" height="1">
    </form>
  </p>
</div>

<script>
  jQuery(document).ready(function() {
    var api_url = "https://api.instagram.com/v1";
    var api_endpoint = "/users/self/media/recent/?access_token=<?php echo get_option('ac_access_token');?>";

    // load the feed
    function request(url) {
      return jQuery.ajax({
        url: url, 
        type: 'GET',
        dataType: 'jsonp',
        progress: function(e) {
          console.log(e);
          // check if we can compute the length
          if(e.lengthComputable) {
            var pct = (e.loaded/e.total) * 100;
            console.log(pct);
          } else {
            console.warn('Content length not reported.');
          }
        }
      });
    }

    function layoutTheFeed(res) {
      for(var i=0; i < res.data.length; i++) {
        var image = '<li>';
        image += '<div style="float: left; width: 200px;">';
        image += '<img src="'+res.data[i].images.low_resolution.url+'" width="200" alt="'+res.data[i].images.low_resolution.url+'">';
        image += '</div>';
        image += '<div style="position: relative; margin-left: 215px;">';
        image += '<p>'+res.data[i].caption.text+'</p>';
        image += '<p>Total likes: '+res.data[i].likes.count+'</p>';
        image += '<p>Total comments: '+res.data[i].comments.count+'</p>';
        image += '<a href="" id="product-'+res.data[i].id+'" data-image="'+res.data[i].images.low_resolution.url+'" data-id="'+res.data[i].id+'" data-caption="'+res.data[i].caption.text+'">Add as Woocommerce product</a>';
        image += '</div>';
        image += '<div style="clear: both;"></div>';
        image += '</li>'; 
          
        jQuery('#container').append(image);

        // create handler for product button
        jQuery('#product-'+res.data[i].id).click(function(e) {
          e.preventDefault();

          var data = {
            action: 'add_product',
            id: jQuery(this).data('id'),
            caption: jQuery(this).data('caption'),
            image_url: jQuery(this).data('image')
          };

          jQuery.post(ajaxurl, data, function(response) {
            if(parseInt(response) > 0) {
              var win = window.open("<?php echo get_site_url(); ?>/wp-admin/post.php?post="+response+"&action=edit", "_blank");
              if(win) {
                win.focus();
              } else {
                jQuery('.thickbox').trigger('click');
              }
            } else {
              alert("Fail to import the photo.");
            }
          });
                    
        });
      }
    }

    request(api_url+api_endpoint).done(function(res) {
      if(typeof res.pagination.next_url !== 'undefined') {
        jQuery('#next_url').val(res.pagination.next_url);
        jQuery('#load_more').show();
      } else {
        jQuery('#load_more').hide();
      }
      
      layoutTheFeed(res);
    });

		// handling the button
    jQuery('#load_more').click(function() {
			var request_pagination = (function (url) {
      	return jQuery.ajax({
        	url: url, 
        	type: 'GET',
					dataType: 'jsonp',
					success: function(res) {
						if(typeof res.pagination.next_url !== 'undefined') {
          		jQuery('#next_url').val(res.pagination.next_url);
          		jQuery('#load_more').show();
        		} else {
          		jQuery('#load_more').hide();
        		} 

        		layoutTheFeed(res);
					}
      	});
    	})(jQuery('#next_url').val());
		});
  });
</script>
<?php
} else {
  wp_redirect(admin_url('admin.php?page=woostagram_connect_settings'));
}
?>

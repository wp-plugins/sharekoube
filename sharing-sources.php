<?php
/**
 * Sharekoube_Twitter
 */
class Sharekoube_Twitter extends Share_Twitter {
	private $smart = true;
	
	public function get_display( $post ) {
		$this->get_parent_options();
		
		if ( $this->smart == 'smart' )
			return '<div class="twitter_button"><iframe allowtransparency="true" frameborder="0" scrolling="no" src="http://platform.twitter.com/widgets/tweet_button.html?url=' . rawurlencode( apply_filters( 'sharing_permalink', get_permalink( $post->ID ), $post->ID, $this->id ) ) . '&counturl=' . rawurlencode( str_replace( 'https://', 'http://', get_permalink( $post->ID ) ) ) . '&count=horizontal&lang=ja&text=' . rawurlencode( $post->post_title ) . ': " style="width:121px; height:20px;"></iframe></div>';
		else
			return $this->get_link( get_permalink( $post->ID ), 'Twitter', __( 'Click to share on Twitter', 'sharekoube' ), 'share=twitter' );
	}
	
	public function get_parent_options() {
		$options = $this->get_options();
		$this->smart = $options[ 'smart' ];
	}
}

/**
 * Sharekoube_Mixi_Check
 */
class Sharekoube_Mixi_Check extends Sharing_Advanced_Source {
	private $smart = false;
	private $button = 'button-1';
	private $check_key = null;
	private $ogp_use = false;
	private $ogp_ns = 'og';
	
	public function __construct( $id, array $settings ) {
		parent::__construct( $id, $settings );
		
		if ( isset( $settings['smart'] ) )
			$this->smart = $settings['smart'];
		
		if ( isset( $settings['button'] ) )
			$this->button = $settings['button'];
		
		if ( isset( $settings['check_key'] ) )
			$this->check_key = $settings['check_key'];
		
		if ( isset( $settings['ogp_use'] ) )
			$this->ogp_use = $settings['ogp_use'];
		
		if ( isset( $settings['ogp_ns'] ) )
			$this->ogp_ns = $settings['ogp_ns'];
	}
	
	public function get_name(){
		return __( 'mixi Check', 'sharekoube' );
	}
	
	public function has_custom_button_style() {
		return $this->smart;
	}
	
	public function display_header(){
		if ( $this->ogp_use ) {
			$ns = $this->ogp_ns;
			if ( is_home() ) {
				echo '<meta property="'.$ns.':title" content="' . get_bloginfo( 'title' ) . '" />'.PHP_EOL;
				echo '<meta property="'.$ns.':description" content="' . get_bloginfo( 'description' ) . '" />'.PHP_EOL;
				echo '<meta property="'.$ns.':type" content="blog" />'.PHP_EOL;
				echo '<meta property="'.$ns.':url" content="' . home_url( '/', 'http' ) . '" />'.PHP_EOL;
			}
			elseif ( is_singular() ) {
				the_post();
				$post = get_post( get_the_ID() );
				if ( has_excerpt() ) {
					$description = strip_tags( $post->post_excerpt );
				} else {
					$description = strip_tags( strip_shortcodes( str_replace( array("\n","\r","\t"), '', $post->post_content ) ) );
					$description = mb_substr( $description, 0 , 100 );
				}
				echo '<meta property="'.$ns.':title" content="' . $post->post_title . '" />'.PHP_EOL;
				echo '<meta property="'.$ns.':description" content="' . $description . '" />'.PHP_EOL;
				echo '<meta property="'.$ns.':type" content="article" />'.PHP_EOL;
				echo '<meta property="'.$ns.':url" content="' . get_permalink() . '" />'.PHP_EOL;
				
				//成年コンテンツ
				//echo '<meta property="mixi:content-rating" content="1" />'.PHP_EOL;
				
				//サムネイル画像
				$images = array();
				if ( has_post_thumbnail() ) {
					list( $src ) = wp_get_attachment_image_src( get_post_thumbnail_id() );
					$images[] = $src;
				}
				preg_match_all( "/\<img[^\>]*[src] *= *[\"\']{0,1}([^\"\'\ >]*)/i", $post->post_title, $matches );
				$images = array_merge( $images, $matches[1] );
				foreach ( $images as $image ) {
					echo '<meta property="'.$ns.':image" content="'.$image.'" />'.PHP_EOL;
				}
				
				//自動取得の制御
				//echo '<meta name="mixi-check-robots" CONTENT="notitle, nodescription, noimage">'.PHP_EOL;
				
				//デバイス別URLの指定 linkでもmetaでも
				//echo '<link rel="mixi-check-alternate" type="text/html" media="mixi-device-(smartphone|mobile|docomo|au|softbank)" href="[記事URL]" />'.PHP_EOL;
				//echo '<meta property="mixi:device-(smartphone|mobile|docomo|au|softbank)" content="[記事URL]" />'.PHP_EOL;
				
				rewind_posts();
			}
			else {
				$title = rtrim( wp_title( '|', false, 'right' ), ' |' );
				$parse_url = parse_url( home_url( '/' ) );
				$url = home_url( preg_replace( '|^'.$parse_url[ 'path' ].'|', '', $_SERVER[ 'REQUEST_URI' ] ), 'http' );
				echo '<meta property="'.$ns.':title" content="' . $title . '" />'.PHP_EOL;
				echo '<meta property="'.$ns.':type" content="blog" />'.PHP_EOL;
				echo '<meta property="'.$ns.':url" content="' . $url . '" />'.PHP_EOL;
			}
		}
	}
	
	public function get_display( $post ) {
		if ( !$this->check_key ) return __( 'Not set mixi check key.', 'sharekoube' );
		if ( $this->smart )
			return '<a href="http://mixi.jp/share.pl" class="mixi-check-button" data-key="'.$this->check_key.'" data-url="'.get_permalink( $post->ID ).'" data-button="'.$this->button.'">Check</a><script type="text/javascript" src="http://static.mixi.jp/js/share.js"></script>';
		else
			return $this->get_link( get_permalink( $post->ID ), __( 'mixi Check', 'sharekoube' ), __( 'Click to share on mixi Check', 'sharekoube' ), 'share=mixi-check' );
	}
	
	public function get_link( $url, $text, $title, $query = '' ) {
		$klasses = array( 'share-'.$this->get_class() );
		
		if ( $this->button_style == 'icon' || $this->button_style == 'icon-text' )
			$klasses[] = 'share-icon';
		
		if ( $this->button_style == 'icon' ) {
			$text = '';
			$klasses[] = 'no-text';
		}
		
		if ( $this->button_style == 'text' )
			$klasses[] = 'no-icon';
		
		if ( !empty( $query ) ) {
			if ( stripos( $url, '?' ) === false )
				$url .= '?'.$query;
			else
				$url .= '&amp;'.$query;
		}
		
		$javascript = "window.open('".$url."','share',['width=632','height=456','location=yes','resizable=yes','toolbar=no','menubar=no','scrollbars=no','status=no'].join(','));";
		
		return sprintf( '<a href="javascript:void(0);" onclick="%s" class="%s" title="%s">%s</a>', $javascript, implode( ' ', $klasses ), $title, $text );
	}
	
	public function process_request( $post, array $post_data ) {
		$post_title = $post->post_title;
		$post_link = apply_filters( 'sharing_permalink', get_permalink( $post->ID ), $post->ID );
		
		$mixi_check_url = '';	
		$mixi_check_url = 'http://mixi.jp/share.pl?u=' . urlencode( $post_link ) . '&k=' . $this->check_key;
		
		// Record stats
		parent::process_request( $post, $post_data );
		
		// Redirect to Twitter
		wp_redirect( $mixi_check_url );
		die();
	}
	
	public function update_options( array $data ) {
		$this->smart = false;
		$this->button = 'button-1';
		$this->check_key = null;
		$this->ogp_use = false;
		$this->ogp_ns = 'og';
		
		if ( isset( $data['smart'] ) )
			$this->smart = $data['smart'];
		
		if ( isset( $data['button'] ) )
			$this->button = $data['button'];
		
		if ( isset( $data['check_key'] ) )
			$this->check_key = $data['check_key'];
		
		if ( isset( $data['ogp_use'] ) )
			$this->ogp_use = true;
		
		if ( isset( $data['ogp_ns'] ) )
			$this->ogp_ns = $data['ogp_ns'];
	}
	
	public function get_options() {
		return array(
			'smart'     => $this->smart,
			'button'    => $this->button,
			'check_key' => $this->check_key,
			'ogp_use'   => $this->ogp_use,
			'ogp_ns'    => $this->ogp_ns,
		);
	}
	
	public function display_options() {
?>
	<div class="input">
		<label><?php _e( 'mixi check key', 'sharekoube' ); ?><br />
		<input type="text" name="check_key" size="10" value="<?php echo esc_attr( $this->check_key ); ?>" /></label>
		<input class="button-secondary" type="submit"value="<?php _e( 'Save', 'sharekoube' ); ?>" />
	</div>
	<div class="input">
		<label><input name="smart" type="checkbox"<?php if ( $this->smart ) echo ' checked="checked"'; ?>/>
		<?php _e( 'Use smart button', 'sharekoube' ); ?></label><br />
		<label><?php _e( 'Button', 'sharekoube' ); ?></label>
		<select name="button">
			<option value="button-1"<?php if ( $this->button == 'button-1' ) echo ' selected="selected"'; ?>><?php _e( 'button-1', 'sharekoube' ); ?></option>
			<option value="button-2"<?php if ( $this->button == 'button-2' ) echo ' selected="selected"'; ?>><?php _e( 'button-2', 'sharekoube' ); ?></option>
			<option value="button-3"<?php if ( $this->button == 'button-3' ) echo ' selected="selected"'; ?>><?php _e( 'button-3', 'sharekoube' ); ?></option>
			<option value="button-4"<?php if ( $this->button == 'button-4' ) echo ' selected="selected"'; ?>><?php _e( 'button-4', 'sharekoube' ); ?></option>
			<option value="button-5"<?php if ( $this->button == 'button-5' ) echo ' selected="selected"'; ?>><?php _e( 'button-5', 'sharekoube' ); ?></option>
		</select>
	</div>
	<div class="input">
		<label><input name="ogp_use" type="checkbox"<?php if ( $this->ogp_use ) echo ' checked="checked"'; ?>/>
		<?php _e( 'OGP meta to output', 'sharekoube' ); ?></label><br />
		<label><?php _e( 'Namespace', 'sharekoube' ); ?></label>
		<select name="ogp_ns">
			<option value="og"<?php if ( $this->ogp_ns == 'og' ) echo ' selected="selected"'; ?>><?php _e( 'og', 'sharekoube' ); ?></option>
			<option value="mixi"<?php if ( $this->ogp_ns == 'mixi' ) echo ' selected="selected"'; ?>><?php _e( 'mixi', 'sharekoube' ); ?></option>
		</select>
	</div>
<?php
	}
	
	public function display_preview() {
?>
	<div class="option option-smart-<?php echo $this->smart ? 'on option-'.$this->button : 'off'; ?>">
		<?php
			if ( !$this->smart ) {
				if ( $this->button_style == 'text' || $this->button_style == 'icon-text' )
					echo $this->get_name();
				else
					echo '&nbsp;';
			}
		?>
	</div>
<?php
	}
}

/**
 * Sharekoube_Hatena_Bookmark
 */
class Sharekoube_Hatena_Bookmark extends Sharing_Advanced_Source {
	private $smart = true;
	private $count = true;
	
	public function __construct( $id, array $settings ) {
		parent::__construct( $id, $settings );
		
		if ( isset( $settings['smart'] ) )
			$this->smart = $settings['smart'];
		
		if ( isset( $settings['count'] ) )
			$this->count = $settings['count'];
	}
	
	public function get_name() {
		return __( 'Hatena Bookmark', 'sharekoube' );
	}
	
	public function get_display( $post ) {
		if ( $this->smart )
			return '<div class="hatena-bookmark-button"><a href="http://b.hatena.ne.jp/entry/'.get_permalink( $post->ID ).'" class="hatena-bookmark-button" data-hatena-bookmark-layout="'.($this->count?'standard':'simple').'" title="このエントリーをはてなブックマークに追加"><img src="http://b.st-hatena.com/images/entry-button/button-only.gif" alt="このエントリーをはてなブックマークに追加" width="20" height="20" style="border: none;" /></a><script type="text/javascript" src="http://b.st-hatena.com/js/bookmark_button.js" charset="utf-8" async="async"></script></div>';
		else
			return $this->get_link( get_permalink( $post->ID ), __( 'Hatena Bookmark', 'sharekoube' ), __( 'Click to share on Hatena Bookmark', 'sharekoube' ), 'share=hatena-bookmark' );
	}
	
	public function get_link( $url, $text, $title, $query = '' ) {
		$klasses = array( 'share-'.$this->get_class() );
		
		if ( $this->button_style == 'icon' || $this->button_style == 'icon-text' )
			$klasses[] = 'share-icon';
		
		if ( $this->button_style == 'icon' ) {
			$text = '';
			$klasses[] = 'no-text';
		}
		
		if ( $this->button_style == 'text' )
			$klasses[] = 'no-icon';
		
		if ( !empty( $query ) ) {
			if ( stripos( $url, '?' ) === false )
				$url .= '?'.$query;
			else
				$url .= '&amp;'.$query;
		}
		
		$javascript = "javascript:(function(){var%20d=(new%20Date);var%20s=document.createElement('script');s.charset='UTF-8';s.src='http://b.hatena.ne.jp/js/Hatena/Bookmark/let.js?'+d.getFullYear()+d.getMonth()+d.getDate();(document.getElementsByTagName('head')[0]||document.body).appendChild(s);})();";
		
		return sprintf( '<a href="%s" class="%s" title="%s">%s</a>', $javascript, implode( ' ', $klasses ), $title, $text );
	}
	
	public function update_options( array $data ) {
		$this->smart = false;
		$this->count = false;
		
		if ( isset( $data['smart'] ) )
			$this->smart = $data['smart'];
		
		if ( isset( $data['count'] ) )
			$this->count = $data['count'];
	}
	
	public function get_options() {
		return array(
			'smart' => $this->smart,
			'count' => $this->count,
		);
	}
	
	public function display_options() {
?>
	<div class="input">
		<label>
			<input name="count" type="checkbox"<?php if ( $this->count ) echo ' checked="checked"'; ?>/>
			<?php _e( 'Include count', 'sharekoube' ); ?>
		</labelt>
	</div>
	<div class="input">
		<label>
			<input name="smart" type="checkbox"<?php if ( $this->smart ) echo ' checked="checked"'; ?>/>
			<?php _e( 'Use smart button', 'sharekoube' ); ?>
		</label>
	</div>
<?php
	}
	
	public function display_preview() {
?>
	<div class="option option-smart-<?php echo $this->smart ? 'on' . ( $this->count ? '' : '-simple' ) : 'off'; ?>">
		<?php
			if ( !$this->smart ) {
				if ( $this->button_style == 'text' || $this->button_style == 'icon-text' )
					echo $this->get_name();
				else
					echo '&nbsp;';
			}
		?>
	</div>
<?php
	}
}

/**
 * Sharekoube_Evernote
 */
class Sharekoube_Evernote extends Sharing_Advanced_Source {
	private $smart = true;
	private $button = 'original';
	private $buttons = array(
		'default' => '/evernote.png',
		'original' => '/evernote-clipper.png',
		'remember' => '/evernote-clipper-remember.png',
		'japanese' => '/evernote-clipper-jp.png',
	);
	
	public function __construct( $id, array $settings ) {
		parent::__construct( $id, $settings );
		
		if ( isset( $settings['smart'] ) )
			$this->smart = $settings['smart'];
		
		if ( isset( $settings['button'] ) )
			$this->button = $settings['button'];
		
		if ( isset( $settings['code'] ) )
			$this->check_key = $settings['code'];
	}
	
	public function get_name() {
		return __( 'Evernote', 'sharekoube' );
	}
	
	public function get_display( $post ) {
		if ( $this->smart ) {
			$post_tags = get_the_tags( $post->ID );
			$tags = array();
			if ( !empty( $post_tags ) ) {
				foreach ( get_the_tags( $post->ID ) as $tag ) {
					$tags[] = $tag->name;
				}
			}
			$options = array(
				'title' => get_the_title( $post->ID ),
				'url' => get_permalink( $post->ID ),
				'suggestTags' => join( ',', $tags ),
				'suggestNotebook' => '',
				'providerName' => get_bloginfo( 'name' ),
				'contentId' => 'content',
				'styling' => 'full',
				'code' => '',// Evernote Affiliate Program
			);
			$_params = array();
			foreach ( $options as $key => $value ) if ( $value ) $_params[] = "{$key}: '{$value}'";
			
			return '<div class="evernote-button-'.$this->button.'"><script type="text/javascript" src="http://static.evernote.com/noteit.js"></script>'
				  .'<a class="share-evernote-button" href="#" onclick="Evernote.doClip({'.join( ',', $_params ).'}); return false;"><img src="'.plugin_dir_url(__FILE__).'/images/'.$this->buttons[ $this->button ].'" alt="'.__( 'Click to share on Evernote', 'sharekoube' ).'" /></a></div>';
		}
		else
			return $this->get_link( get_permalink( $post->ID ), __( 'Evernote', 'sharekoube' ), __( 'Click to share on Evernote', 'sharekoube' ), 'share=evernote' );
	}
	
	public function get_link( $url, $text, $title, $query = '' ) {
		$klasses = array( 'share-'.$this->get_class() );
		
		if ( $this->button_style == 'icon' || $this->button_style == 'icon-text' )
			$klasses[] = 'share-icon';
		
		if ( $this->button_style == 'icon' ) {
			$text = '';
			$klasses[] = 'no-text';
		}
		
		if ( $this->button_style == 'text' )
			$klasses[] = 'no-icon';
		
		if ( !empty( $query ) ) {
			if ( stripos( $url, '?' ) === false )
				$url .= '?'.$query;
			else
				$url .= '&amp;'.$query;
		}
		
		$permalink = get_permalink();
		$post_title = get_the_title();
		
		$javascript = "http://www.evernote.com/clip.action?url=$permalink&title=$post_title";
		
		return sprintf( '<a href="%s" class="%s" title="%s">%s</a>', $javascript, implode( ' ', $klasses ), $title, $text );
	}
	
	public function update_options( array $data ) {
		$this->smart = true;
		$this->button = 'original';
		$this->code = null;
		
		if ( isset( $data['button'] ) )
			$this->button = $data['button'];
		
		if ( isset( $data['code'] ) )
			$this->check_key = $data['code'];
		
		$this->smart = ( $this->button != 'default' );
	}
	
	public function get_options() {
		return array(
			'smart' => $this->smart,
			'button' => $this->button,
			'code' => $this->code
		);
	}
	
	public function display_options() {
?>
	<div class="input">
		<select name="button">
			<option value="default"<?php if ( $this->button == 'default' ) echo ' selected="selected"'; ?>><?php _e( 'default', 'sharekoube' ); ?></option>
			<option value="original"<?php if ( $this->button == 'original' ) echo ' selected="selected"'; ?>><?php _e( 'original', 'sharekoube' ); ?></option>
			<option value="remember"<?php if ( $this->button == 'remember' ) echo ' selected="selected"'; ?>><?php _e( 'remember', 'sharekoube' ); ?></option>
			<option value="japanese"<?php if ( $this->button == 'japanese' ) echo ' selected="selected"'; ?>><?php _e( 'japanese', 'sharekoube' ); ?></option>
		</select>
	</div>
<?php
	}
	
	public function display_preview() {
?>
	<div class="option option-smart-<?php echo $this->smart ? 'on option-'.$this->button : 'off'; ?>">
		<?php
			if ( !$this->smart ) {
				if ( $this->button_style == 'text' || $this->button_style == 'icon-text' )
					echo $this->get_name();
				else
					echo '&nbsp;';
			}
		?>
	</div>
<?php
	}
}

/**
 * Sharekoube_Google_Buzz 
 */
class Sharekoube_Google_Buzz extends Sharing_Advanced_Source {
	private $smart = true;
	private $button = 'small-count';
	private $buttons = array(
		'default',
		//'link',
		'small-count',
		'small-button',
	);
	
	public function __construct( $id, array $settings ) {
		parent::__construct( $id, $settings );

		if ( isset( $settings['smart'] ) )
			$this->smart = $settings['smart'];
		
		if ( isset( $settings['button'] ) )
			$this->button = $settings['button'];
	}
	
	public function get_name() {
		return __( 'Google Buzz', 'sharekoube' );
	}

	public function get_display( $post ) {
		if ( $this->smart == 'smart' )
			return '<div class="buzz_button '.$this->button.'"><a title="'.__( 'Click to share on Google Buzz', 'sharekoube' ).'" class="google-buzz-button" href="http://www.google.com/buzz/post" data-button-style="'.$this->button.'" data-locale="ja"></a><script type="text/javascript" src="http://www.google.com/buzz/api/button.js"></script></div>';
		else
			return $this->get_link( get_permalink( $post->ID ), __( 'Google Buzz', 'sharekoube' ), __( 'Click to share on Google Buzz', 'sharekoube' ), 'share=google-buzz' );
	}
	
	public function process_request( $post, array $post_data ) {
		$post_title = $post->post_title;
		$post_link = apply_filters( 'sharing_permalink', get_permalink( $post->ID ), $post->ID, $this->id );
		
		$googlebuzz_url = '';
		if ( function_exists( 'mb_stripos' ) )
			$mb = true;
		else
			$mb = false;
		
		if ( ( $mb && ( mb_strlen( $post_title ) ) > 140 ) || ( !$mb && ( strlen( $post_title ) ) > 140 ) ) {
			if ( $mb ) {
				$googlebuzz_url = 'http://www.google.com/buzz/post?message=' . rawurlencode( ( mb_substr( $post_title, 0, (140 - 4 ) ) ) . '...' ) . '&url=' . rawurlencode( $post_link );
			} else {
				$googlebuzz_url = 'http://www.google.com/buzz/post?message=' . rawurlencode( ( substr( $post_title, 0, (140 - 4 ) ) ) . '...' ) . '&url=' . rawurlencode( $post_link );
			}
		}
		else {
			$googlebuzz_url = 'http://www.google.com/buzz/post?message=' . rawurlencode( $post_title ) . '&url=' . rawurlencode( $post_link );
		}
		
		// Record stats
		parent::process_request( $post, $post_data );
		
		// Redirect to Twitter
		wp_redirect( $googlebuzz_url );
		die();
	}
	
	public function has_custom_button_style() {
		return $this->smart;
	}

	public function display_preview() {
?>
	<div class="option option-smart-<?php echo $this->smart ? 'on option-'.$this->button : 'off'; ?>">
		<?php
			if ( !$this->smart ) {
				if ( $this->button_style == 'text' || $this->button_style == 'icon-text' )
					echo $this->get_name();
				else
					echo '&nbsp;';
			}
		?>
	</div>
<?php
	}
	
	public function update_options( array $data ) {
		$this->smart = true;
		$this->button = 'small-count';
		
		if ( isset( $data['button'] ) )
			$this->button = $data['button'];
		
		$this->smart = ( $this->button != 'default' );
	}

	public function get_options() {
		return array(
			'smart' => $this->smart,
			'button' => $this->button,
		);
	}

	public function display_options() {
?>
	<div class="input">
		<select name="button">
			<option value="default"<?php if ( $this->button == 'default' ) echo ' selected="selected"'; ?>><?php _e( 'default', 'sharekoube' ); ?></option>
			<option value="small-count"<?php if ( $this->button == 'small-count' ) echo ' selected="selected"'; ?>><?php _e( 'small-count', 'sharekoube' ); ?></option>
			<option value="small-button"<?php if ( $this->button == 'small-button' ) echo ' selected="selected"'; ?>><?php _e( 'small-button', 'sharekoube' ); ?></option>
		</select>
	</div>
<?php
	}
}

/**
 * Sharekoube_Google_Plus
 */
class Sharekoube_Google_Plus extends Sharing_Advanced_Source {
	private $smart = true;
	private $count = true;
	private $button = 'medium';
	private $buttons = array(
		'small',
		'medium',
		'standard',
	);
	
	public function __construct( $id, array $settings ) {
		parent::__construct( $id, $settings );

		if ( isset( $settings['smart'] ) )
			$this->smart = $settings['smart'];
		
		if ( isset( $settings['count'] ) )
			$this->count = $settings['count'];
		
		if ( isset( $settings['button'] ) )
			$this->button = $settings['button'];
	}
	
	public function get_name() {
		return 'Google +1';
	}

	public function get_display( $post ) {
		if ( $this->smart == 'smart' )
			return '<div class="google-plus-button '.$this->button.($this->count?' count':'').'"><script type="text/javascript" src="https://apis.google.com/js/plusone.js">{lang: "ja"}</script><g:plusone '.( $this->button != 'standard' ? 'size="'.$this->button.'" ' : '').( $this->count ? '' : 'count="false"' ).'href="'.get_permalink( $post->ID ).'"></g:plusone></div>';
		else
			return $this->get_link( get_permalink( $post->ID ), 'Google+1', 'クリックしてGoogle+1に投稿', 'share=google-plus' );
	}	
	
	public function process_request( $post, array $post_data ) {
		
		echo 'wakaranai';
		
		die();
	}
	
	public function has_custom_button_style() {
		return $this->smart;
	}

	public function display_preview() {
?>
	<div class="option option-smart-<?php echo $this->smart ? 'on option-'.$this->button : 'off'; echo $this->count ? ' with-count' : '' ?>">
		<?php
			if ( !$this->smart ) {
				if ( $this->button_style == 'text' || $this->button_style == 'icon-text' )
					echo $this->get_name();
				else
					echo '&nbsp;';
			}
		?>
	</div>
<?php
	}
	
	public function update_options( array $data ) {
		$this->smart = false;
		$this->count = false;
		$this->button = 'small-count';
		
		if ( isset( $data['button'] ) )
			$this->button = $data['button'];
		
		if ( $this->button != 'default' )
			$this->smart = true;
		
		if ( isset( $data['count'] ) )
			$this->count = $data['count'];
	}

	public function get_options() {
		return array(
			'smart' => $this->smart,
			'count' => $this->count,
			'button' => $this->button,
		);
	}

	public function display_options() {
?>
	<div class="input">
		<label>
			<input name="count" type="checkbox"<?php if ( $this->count ) echo ' checked="checked"'; ?>/>
			<?php _e( 'Include count', 'sharekoube' ); ?>
		</labelt>
	</div>
	<div class="input">
		<select name="button">
			<option value="small"<?php if ( $this->button == 'small' ) echo ' selected="selected"'; ?>><?php _e( 'small', 'sharekoube' ); ?></option>
			<option value="medium"<?php if ( $this->button == 'medium' ) echo ' selected="selected"'; ?>><?php _e( 'medium', 'sharekoube' ); ?></option>
			<option value="standard"<?php if ( $this->button == 'standard' ) echo ' selected="selected"'; ?>><?php _e( 'standard', 'sharekoube' ); ?></option>
		</select>
	</div>
<?php
	}
}
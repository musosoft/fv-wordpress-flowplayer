<?php

class FV_Player_SEO {
  
  var $can_seo = false;
  
  public function __construct() {
    add_filter('fv_flowplayer_args_pre', array($this, 'should_i'), 10, 3 );
    add_filter('fv_flowplayer_attributes', array($this, 'single_attributes'), 10, 3 );
    add_filter('fv_player_playlist_attributes', array($this, 'playlist_attributes'), 10, 3 );
    add_filter('fv_flowplayer_inner_html', array($this, 'single_video_seo'), 10, 2 );
    add_filter('fv_player_item_html', array($this, 'playlist_video_seo'), 10, 6 );

  }
  
  function playlist_attributes( $attributes, $media, $fv_fp ) {
    if( !$fv_fp->aCurArgs['playlist'] || !$this->can_seo ) {
      return $attributes;
    }
    
    $attributes['itemprop'] = 'video';
    $attributes['itemscope'] = '';
    $attributes['itemtype'] = 'http://schema.org/VideoObject';
    
    return $attributes;
  }
  
  function single_attributes( $attributes, $media, $fv_fp ) {
    if( $fv_fp->aCurArgs['playlist'] || !$this->can_seo ) {
      return $attributes;
    }
    
    $attributes['itemprop'] = 'video';
    $attributes['itemscope'] = '';
    $attributes['itemtype'] = 'http://schema.org/VideoObject';
    
    return $attributes;
  }
  
  function get_markup( $title, $description, $splash, $url ) {
    if( !$title ) {
      $title = get_the_title();
    }
    
    if( !$description ) { //  todo: read this from shortcode
      $description = get_post_meta(get_the_ID(),'_aioseop_description', true );
    }
    $post_excerpt = get_the_excerpt();
    if( !$description && strlen($post_excerpt) > 0 ) {
      $aWords = explode( ' ', $post_excerpt, 10 );
      unset($aWords[count($aWords)-1]);
      $description = implode( ' ', $aWords );
    }
    if( !$description ) {
      $description = get_option('blogdescription');
    }
    
    if( !$url ) {
      $url = get_permalink();
    }
    
    if( stripos($splash,'://') === false ) {
      $splash = home_url($splash);
    }
    
    return '<meta itemprop="name" content="'.esc_attr($title).'" />
        <meta itemprop="description" content="'.esc_attr($description).'" />
        <meta itemprop="thumbnailUrl" content="'.esc_attr($splash).'" />
        <meta itemprop="contentURL" content="'.esc_attr($url).'" />
        <meta itemprop="uploadDate" content="'.esc_attr(get_the_modified_date('Y-m-d')).'" />';        
  }
  
  function playlist_video_seo( $sHTML, $aArgs, $sSplashImage, $sItemCaption, $aPlayer, $index ) {
    if( $this->can_seo ) {
      $sHTML = str_replace( '</a>', $this->get_markup($sItemCaption,false,$sSplashImage,false).'</a>', $sHTML );
    }
    return $sHTML;
  }
  
  function should_i( $args ) {
    global $fv_fp;
    if( !get_permalink() || !$fv_fp->get_splash() ) {
      $this->can_seo = false;
    }
    
    $this->can_seo = true;    
    return $args;
  }
  
  function single_video_seo( $html, $fv_fp ) {
    if( $this->can_seo ) {
      if( !$fv_fp->aCurArgs['playlist'] ) {        
        //  todo: use iframe or video link URL
        $html .= "\n".$this->get_markup($fv_fp->aCurArgs['caption'],false,$fv_fp->get_splash(),false)."\n";    
      }
    }
    return $html;
  }  

}

$FV_Player_SEO = new FV_Player_SEO();

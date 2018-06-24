<?php
/*  FV Wordpress Flowplayer - HTML5 video player with Flash fallback
    Copyright (C) 2013  Foliovision

    This program is free software: you can redistribute it and/or modify
    it under the terms of the GNU General Public License as published by
    the Free Software Foundation, either version 3 of the License, or
    (at your option) any later version.

    This program is distributed in the hope that it will be useful,
    but WITHOUT ANY WARRANTY; without even the implied warranty of
    MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE.  See the
    GNU General Public License for more details.

    You should have received a copy of the GNU General Public License
    along with this program.  If not, see <http://www.gnu.org/licenses/>.
*/

// video instance with options that's stored in a DB
class FV_Player_Db_Shortcode_Player_Video {

  private
    $id, // automatic ID for the video
    $is_valid = false, // used when loading the video from DB to determine whether we've found it
    $caption, // optional video caption
    $end, // allows you to show only a specific part of a video
    $mobile, // mobile (smaller-sized) version of this video
    $rtmp, // optional RTMP server URL
    $rtmp_path, // if RTMP is set, this will have the path on the server to the RTMP stream
    $splash, // URL to the splash screen picture
    $splash_text, // an optional splash screen text
    $src, // the main video source
    $src_1, // alternative source path #1 for the video
    $src_2, // alternative source path #2 for the video
    $start, // allows you to show only a specific part of a video
    $DB_Shortcode_Instance = null,
    $meta_data = null; // object of this video's meta data

  private static $db_table_name;
  
  /**
   * @return int
   */
  public function getId() {
    return $this->id;
  }

  /**
   * @return string
   */
  public function getCaption() {
    return $this->caption;
  }

  /**
   * @return string
   */
  public function getEnd() {
    return $this->end;
  }

  /**
   * @return string
   */
  public function getMobile() {
    return $this->mobile;
  }

  /**
   * @return string
   */
  public function getRtmp() {
    return $this->rtmp;
  }

  /**
   * @return string
   */
  public function getRtmpPath() {
    return $this->rtmp_path;
  }

  /**
   * @return string
   */
  public function getSplash() {
    return $this->splash;
  }

  /**
   * @return string
   */
  public function getSplashText() {
    return $this->splash_text;
  }

  /**
   * @return string
   */
  public function getSrc() {
    return $this->src;
  }

  /**
   * @return string
   */
  public function getSrc1() {
    return $this->src_1;
  }

  /**
   * @return string
   */
  public function getSrc2() {
    return $this->src_2;
  }

  /**
   * @return string
   */
  public function getStart() {
    return $this->start;
  }

  /**
   * @return bool
   */
  public function getIsValid() {
    return $this->is_valid;
  }

  /**
   * Initializes database name, including WP prefix
   * once WPDB class is initialized.
   *
   * @return string Returns the actual table name for this ORM class.
   */
  public static function init_db_name() {
    global $wpdb;

    self::$db_table_name = $wpdb->prefix.'fv_player_videos';
    return self::$db_table_name;
  }

  /**
   * Checks for DB tables existence and creates it as necessary.
   *
   * @param $wpdb The global WordPress database object.
   */
  private function initDB($wpdb) {
    global $fv_fp;

    self::init_db_name();

    if (!$fv_fp->_get_option('video_model_db_checked')) {
      if ( $wpdb->get_var( "SHOW TABLES LIKE '" . self::$db_table_name . "'" ) != self::$db_table_name ) {
        $sql = "
  CREATE TABLE `" . self::$db_table_name . "` (
    `id` int(10) UNSIGNED NOT NULL AUTO_INCREMENT,
    `src` varchar(255) COLLATE utf8mb4_unicode_ci NOT NULL COMMENT 'the main video source',
    `src_1` varchar(255) COLLATE utf8mb4_unicode_ci NOT NULL COMMENT 'alternative source path #1 for the video',
    `src_2` varchar(255) COLLATE utf8mb4_unicode_ci NOT NULL COMMENT 'alternative source path #2 for the video',
    `splash` varchar(255) COLLATE utf8mb4_unicode_ci NOT NULL COMMENT 'URL to the splash screen picture',
    `splash_text` varchar(255) COLLATE utf8mb4_unicode_ci NOT NULL COMMENT 'an optional splash screen text',
    `caption` varchar(255) COLLATE utf8mb4_unicode_ci NOT NULL COMMENT 'optional video caption',
    `end` varchar(255) COLLATE utf8mb4_unicode_ci NOT NULL COMMENT 'allows you to show only a specific part of a video',
    `mobile` varchar(255) COLLATE utf8mb4_unicode_ci NOT NULL COMMENT 'mobile (smaller-sized) version of this video',
    `rtmp` varchar(255) COLLATE utf8mb4_unicode_ci NOT NULL COMMENT 'optional RTMP server URL',
    `rtmp_path` varchar(255) COLLATE utf8mb4_unicode_ci NOT NULL COMMENT 'if RTMP is set, this will have the path on the server to the RTMP stream',
    `start` varchar(255) COLLATE utf8mb4_unicode_ci NOT NULL COMMENT 'allows you to show only a specific part of a video',
    PRIMARY KEY (`id`),
    KEY `src` (`src`)
  )" . $wpdb->get_charset_collate() . ";";
        require_once( ABSPATH . 'wp-admin/includes/upgrade.php' );
        dbDelta( $sql );
        $fv_fp->_set_option('video_model_db_checked', 1);
      }
    }
  }

  /**
   * FV_Player_Db_Shortcode_Player_Video constructor.
   *
   * @param int $id         ID of video to load data from the DB for.
   * @param array $options  Options for a newly created video that will be stored in a DB.
   * @param FV_Player_Db_Shortcode $DB_Shortcode Instance of the DB shortcode global object that handles caching
   *                        of videos, players and their meta data.
   *
   * @throws Exception When no valid ID nor options are provided.
   */
  function __construct($id, $options = array(), $DB_Shortcode = null) {
    global $wpdb;

    if ($DB_Shortcode) {
      $this->DB_Shortcode_Instance = $DB_Shortcode;
    }

    $this->initDB($wpdb);
    $multiID = is_array($id);

    // if we've got options, fill them in instead of querying the DB,
    // since we're storing new video into the DB in such case
    if (is_array($options) && count($options) && !isset($options['db_options'])) {
      foreach ($options as $key => $value) {
        if (property_exists($this, $key)) {
          if ($key !== 'id') {
            $this->$key = $value;
          } else {
            // ID cannot be set, as it's automatically assigned to all new videos
            trigger_error('ID of a newly created DB video was provided but will be generated automatically.');
          }
        } else {
          // generate warning
          trigger_error('Unknown property for new DB video: ' . $key);
        }
      }

      $this->is_valid = true;
    } else if ($multiID || (is_numeric($id) && $id > 0)) {
      $cache = ($DB_Shortcode ? $DB_Shortcode->getVideosCache() : array());

      // no options, load data from DB
      if ($multiID) {
        // make sure we have numeric IDs
        foreach ($id as $id_key => $id_value) {
          $id[$id_key] = (int) $id_value;
        }

        // load multiple videos via their IDs but a single query and return their values
        $video_data = $wpdb->get_results('
          SELECT
            '.($options && !empty($options['db_options']) && !empty($options['db_options']['select_fields']) ? 'id,'.$options['db_options']['select_fields'] : '*').'
          FROM
            '.self::$db_table_name.'
          WHERE
            id IN('. implode(',', $id).')'.
            ($options && !empty($options['db_options']) && !empty($options['db_options']['order_by']) ? ' ORDER BY '.$options['db_options']['order_by'].(!empty($options['db_options']['order']) ? ' '.$options['db_options']['order'] : '') : '').
            ($options && !empty($options['db_options']) && isset($options['db_options']['offset']) && isset($options['db_options']['per_page']) ? ' LIMIT '.$options['db_options']['offset'].', '.$options['db_options']['per_page'] : '')
        );
      } else {
        // load a single video
        $video_data = $wpdb->get_row('
          SELECT
            '.($options && !empty($options['db_options']) && !empty($options['db_options']['select_fields']) ? 'id,'.$options['db_options']['select_fields'] : '*').'
          FROM
            '.self::$db_table_name.'
          WHERE
            id = '. $id.
            ($options && !empty($options['db_options']) && !empty($options['db_options']['order_by']) ? ' ORDER BY '.$options['db_options']['order_by'].(!empty($options['db_options']['order']) ? ' '.$options['db_options']['order'] : '') : '').
            ($options && !empty($options['db_options']) && isset($options['db_options']['offset']) && isset($options['db_options']['per_page']) ? ' LIMIT '.$options['db_options']['offset'].', '.$options['db_options']['per_page'] : '')
        );
      }

      if ($video_data) {
        // single ID, just populate our own data
        if (!$multiID) {
          // fill-in our internal variables, as they have the same name as DB fields (ORM baby!)
          foreach ( $video_data as $key => $value ) {
            $this->$key = $value;
          }

          // cache this video in DB Shortcode object
          if ($DB_Shortcode) {
            $cache[$this->id] = $this;
          }
        } else {
          // multiple IDs, create new video objects for each of them except the first one,
          // for which we'll use this instance
          $first_done = false;
          foreach ($video_data as $db_record) {
            if (!$first_done) {
              // fill-in our internal variables
              foreach ( $db_record as $key => $value ) {
                $this->$key = $value;
              }

              $first_done = true;

              // cache this video in DB Shortcode object
              if ($DB_Shortcode) {
                $cache[$this->id] = $this;
              }
            } else {
              // create a new video object and populate it with DB values
              $record_id = $db_record->id;
              // if we don't unset this, we'll get warnings
              unset($db_record->id);
              $video_object = new FV_Player_Db_Shortcode_Player_Video(null, get_object_vars($db_record), $this->DB_Shortcode_Instance);
              $video_object->link2db($record_id);

              // cache this video in DB Shortcode object
              if ($DB_Shortcode) {
                $cache[$record_id] = $video_object;
              }
            }
          }
        }
        $this->is_valid = true;
      }
    } else {
      throw new \Exception('No options nor a valid ID was provided for DB video instance.');
    }

    // update cache, if changed
    if (isset($cache)) {
      $this->DB_Shortcode_Instance->setVideosCache($cache);
    }
  }

  /**
   * Makes this video linked to a record in database.
   * This is used when loading multiple videos in the constructor,
   * so we can return them as objects from the DB and any saving will
   * not insert their duplicates.
   *
   * @param int  $id        The DB ID to which we'll link this video.
   * @param bool $load_meta If true, the meta data will be loaded for the video from database.
   *                        Used when loading multiple videos at once with the array $id constructor parameter.
   *
   * @throws Exception When the underlying Meta object throws.
   */
  public function link2db($id, $load_meta = false) {
    $this->id = (int) $id;

    if ($load_meta) {
      $this->meta_data = new FV_Player_Db_Shortcode_Player_Video_Meta(null, array('id_video' => array($id)), $this->DB_Shortcode_Instance);
    }
  }

  /**
   * This method will manually link meta data to the video.
   * Used when not using save() method to link meta data to video while saving it
   * into the database (i.e. while previewing etc.)
   *
   * @param FV_Player_Db_Shortcode_Player_Video_Meta $meta_data The meta data object to link to this video.
   *
   * @throws Exception When an underlying meta data object throws an exception.
   */
  public function link2meta($meta_data) {
    if (is_array($meta_data) && count($meta_data)) {
      // we have meta, let's insert that
      $first_done = false;
      foreach ($meta_data as $meta_record) {
        // create new record in DB
        $meta_object = new FV_Player_Db_Shortcode_Player_Video_Meta(null, $meta_record, $this->DB_Shortcode_Instance);

        // link to DB, if the meta record has an ID
        if (!empty($meta_record['id'])) {
          $meta_object->link2db($meta_record['id']);
        }

        if (!$first_done) {
          $this->meta_data = array($meta_object);
          $first_done = true;
        } else {
          $this->meta_data[] = $meta_object;
        }
      }
    } else if ($meta_data === -1) {
      $this->meta_data = -1;
    }
  }

  /**
   * Searches for a player via custom query.
   * Used in plugins such as HLS which will
   * provide video src data but not ID to search for.
   *
   * @param bool $like   The LIKE part for the database query.
   * @param null $fields Fields to return for this search.
   *
   * @return bool Returns true if any data were loaded, false otherwise.
   */
  public function searchBySrc($like = false, $fields = null) {
    global $wpdb;

    $row = $wpdb->get_row("SELECT ". ($fields ? esc_sql($fields) : '*') ." FROM `" . self::$db_table_name . "` WHERE `src` ". ($like ? 'LIKE "%'.esc_sql($this->src).'%"' : '="'.esc_sql($this->src).'"') ." ORDER BY id DESC");

    if (!$row) {
      return false;
    } else {
      // load up all values for this video
      foreach ($row as $key => $value) {
        if (property_exists($this, $key)) {
          $this->$key = $value;
        }
      }

      return true;
    }
  }

  /**
   * Returns all options data for this video.
   *
   * @return array Returns all options data for this video.
   */
  public function getAllDataValues() {
    $data = array();
    foreach (get_object_vars($this) as $property => $value) {
      if ($property != 'is_valid' && $property != 'db_table_name' && $property != 'DB_Shortcode_Instance' && $property != 'meta_data') {
        $data[$property] = $value;
      }
    }

    return $data;
  }

  /**
   * Returns meta data for this video.
   *
   * @return FV_Player_Db_Shortcode_Player_Video_Meta[] Returns all meta data for this video.
   * @throws Exception When an underlying meta data object throws an exception.
   */
  public function getMetaData() {
    // meta data already loaded and present, return them
    if ($this->meta_data && $this->meta_data !== -1) {
      // meta data will be an array if we filled all of them at once
      // from database at the time when player is initially created
      if (is_array($this->meta_data)) {
        return $this->meta_data;
      } else if ( count($this->DB_Shortcode_Instance->getVideoMetaCache()) ) {
        return $this->DB_Shortcode_Instance->getVideoMetaCache();
      } else {
        if ($this->meta_data && $this->meta_data->getIsValid()) {
          return array( $this->meta_data );
        } else {
          return array();
        }
      }
    } else if ($this->meta_data === null) {
      // meta data not loaded yet - load them now
      $this->meta_data = new FV_Player_Db_Shortcode_Player_Video_Meta(null, array('id_video' => array($this->id)), $this->DB_Shortcode_Instance);

      // set meta data to -1, so we know we didn't get any meta data for this video
      if (!$this->meta_data->getIsValid() && !count($this->DB_Shortcode_Instance->getVideoMetaCache())) {
        $this->meta_data = -1;
        return array();
      } else {
        if (count($this->DB_Shortcode_Instance->getVideoMetaCache())) {
          return $this->DB_Shortcode_Instance->getVideoMetaCache();
        } else {
          if ($this->meta_data && $this->meta_data->getIsValid()) {
            return array( $this->meta_data );
          } else {
            return array();
          }
        }
      }
    } else {
      return array();
    }
  }

  /**
   * Stores new video instance or updates and existing one
   * in the database.
   *
   * @param array $meta_data An optional array of key-value objects
   *                         with possible meta data for this video.
   *
   * @return bool|int Returns record ID if successful, false otherwise.
   * @throws Exception When the underlying metadata object throws.
   */
  public function save($meta_data = array()) {
    global $wpdb;

    // prepare SQL
    $is_update   = ($this->id ? true : false);
    $sql         = ($is_update ? 'UPDATE' : 'INSERT INTO').' '.self::$db_table_name.' SET ';
    $data_keys   = array();
    $data_values = array();

    foreach (get_object_vars($this) as $property => $value) {
      if ($property != 'id' && $property != 'is_valid' && $property != 'db_table_name' && $property != 'DB_Shortcode_Instance' && $property != 'meta_data') {
        $data_keys[] = $property . ' = %s';
        $data_values[] = $value;
      }
    }

    $sql .= implode(',', $data_keys);

    if ($is_update) {
      $sql .= ' WHERE id = ' . $this->id;
    }

    $wpdb->query( $wpdb->prepare( $sql, $data_values ));

    if (!$is_update) {
      $this->id = $wpdb->insert_id;
    }

    if (!$wpdb->last_error) {
      // check for any meta data
      if (is_array($meta_data) && count($meta_data)) {
        // we have meta, let's insert that
        foreach ($meta_data as $meta_record) {
          // add our video ID
          $meta_record['id_video'] = $this->id;

          // create new record in DB
          $meta_object = new FV_Player_Db_Shortcode_Player_Video_Meta(null, $meta_record, $this->DB_Shortcode_Instance);

          // add meta data ID
          if ($is_update) {
            $meta_object->link2db($meta_record['id']);
          }

          $meta_object->save();
          $this->meta_data = $meta_object;
        }
      }

      // add this meta into cache
      $cache = $this->DB_Shortcode_Instance->getVideosCache();
      $cache[$this->id] = $this;
      $this->DB_Shortcode_Instance->setVideosCache($cache);

      return $this->id;
    } else {
      /*var_export($wpdb->last_error);
      var_export($wpdb->last_query);*/
      return false;
    }
  }

  /**
   * Removes video instance from the database.
   *
   * @return bool Returns true if the delete was successful, false otherwise.
   */
  public function delete() {
    // not a DB video? no delete
    if (!$this->is_valid) {
      return false;
    }

    global $wpdb;

    $wpdb->delete(self::$db_table_name, array('id' => $this->id));

    if (!$wpdb->last_error) {
      // remove this meta from cache
      $cache = $this->DB_Shortcode_Instance->getVideosCache();
      if (isset($cache[$this->id])) {
        unset($cache[$this->id]);
        $this->DB_Shortcode_Instance->setVideosCache($cache);
      }

      return true;
    } else {
      /*var_export($wpdb->last_error);
      var_export($wpdb->last_query);*/
      return false;
    }
  }
}

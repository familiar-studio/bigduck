<?php

  function get_taxonomy_icon_png($svgUrl) {
    $explodedUrl = explode(".", $svgUrl);
    $last = array_pop($explodedUrl);
    return implode(".", $explodedUrl) . '.png';
  }

  function get_insight_data($insight, $is_featured = false) {
    $meta = get_fields($insight);
    $body = $meta['body'];
    $firstTextBlock = null;
    if ($body) {
      foreach($body as $block){
        if ($block['acf_fc_layout'] == 'text') {
          $firstTextBlock = implode("</p>", array_slice(explode("</p>", $block['text']), 0, 2));
          break;
        };
      }
    }
    $imageUrl = $meta['featured_image'];
    $explodedUrl = explode(".", $meta['featured_image']);
    $last = array_pop($explodedUrl);
    $sizeString = $is_featured ? '-585x296' : '-235x265';
    $image = implode(".", $explodedUrl) . $sizeString . '.' . $last;
    $author = $meta['author'][0];
    $headshot = null;
    $author_name = null;
    if ($author) {
      $headshot = get_field('headshot', 'user_' . $author['ID'])['sizes']['small-thumbnail'];
      $author_name = $author['display_name'];
    } else if ($meta['guest_author_name']) {
      $headshot = 'https://bigducknyc.com/wp-content/uploads/2017/06/guest_blogger-35x35.png';
      $author_name = $meta['guest_author_name'];
    }
    $title = $insight->post_title;

    $topic = wp_get_post_terms($insight->ID, 'topic')[0];
    if ($topic->slug == 'campaigns') {
      $icon = 'https://bigducknyc.com/wp-content/uploads/2017/09/campaigns.png';
    } else if ($topic->slug == 'teams') {
      $icon = 'https://bigducknyc.com/wp-content/uploads/2017/09/teams.png';
    } else if ($topic->slug == 'brands') {
      $icon = 'https://bigducknyc.com/wp-content/uploads/2017/09/brands.png';
    } else {
      $icon = $topic ? get_taxonomy_icon_png(get_fields($topic)['icon']) : null;
    }
    $slug = 'https://bigducknyc.com/insights/' . $insight->post_name;

    return array(
      'title' => $title,
      'image' => $image,
      'text' =>  $firstTextBlock,
      'slug' => $slug,
      'imageUrl' => $imageUrl,
      'author' => $author_name,
      'headshot' => $headshot,
      'topic' => array(
        'title' => $topic->name,
        'image' => $icon
      )
    );
  }

  //$slug = $_GET["email"];

  $uri_path = parse_url($_SERVER['REQUEST_URI'], PHP_URL_PATH);
  $uri_segments = explode('/', $uri_path);

  $slug = $uri_segments[2];

  // echo $slug;

  $args = array(
    'name' => $slug,
    'post_type' => 'bd_email',
    'post_status' => 'publish',
    'numberposts' => 1
  );

  function get_event_data($event) {
    $meta = get_fields($event);
    $category = wp_get_post_terms($event->ID, 'event_category')[0];
    if ($category->slug == 'at-big-duck' || $category->slug == 'open-house') {
      $icon = 'https://bigducknyc.com/wp-content/uploads/2017/09/open-house.png';
    } else if ($category->slug == 'webinars') {
      $icon = 'https://bigducknyc.com/wp-content/uploads/2017/09/webinar-1.png';
    } else if ($category->slug == 'workshop')  {
      $icon = 'https://bigducknyc.com/wp-content/uploads/2017/09/workshop.png';
    } else {
      $icon = $category ? get_taxonomy_icon_png(get_fields($category)['taxonomy-icon']) : null;
    }
    $headshot = null;
    if (count($meta['related_team_members']) > 0 && is_array($meta['related_team_members'])){
      $relatedTeamMember = $meta['related_team_members'][0];
      if ($relatedTeamMember) {
        $headshot = get_field('headshot', 'user_' . $relatedTeamMember['ID'])['sizes']['small-thumbnail'];
      }
    } elseif (count($meta['guest_speakers']) > 0){
      $relatedTeamMember = array(
        'display_name' => $meta['guest_speakers'][0]['speaker_name']
      );
    }

    $date = date_create($event->start_time);
    return array(
      'title' => $event->post_title,
      'url' => 'https://bigducknyc.com/events/' . $event->post_name,
      'month' => $date->format('M'),
      'day' => $date->format('j'),
      'category' => array(
        'name' => $category->name,
        'icon' => $icon
      ),
      'author' => $relatedTeamMember,
      'headshot' => $headshot
    );
  }

  $emails = get_posts($args);
  if( $emails) {
    $email = $emails[0];
    $id = $email->ID;
    $meta = get_fields($email);
    $mainInsight = $meta['main_insight'];
    $mainInsightData = get_insight_data($mainInsight, true);
    $insights = $meta['insights'];
    if ($insights) {
      foreach($insights as $insight) {
        $insightData[] = get_insight_data($insight['insight']);
      }
    }
    $events = get_field( 'events', $id);
    if ($events) {
      foreach($events as $event) {
        $eventsData[] = get_event_data($event['event']);
      }
    }
    $sections = get_field( 'sections', $id);
    $preview = get_field( 'preiew', $id);
  };

?>

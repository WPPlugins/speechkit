<?php


class SpeechkitAPI {

  function __construct($api_key, $backend, $news_site_id, $voice_id) {
    $this->api_key = $api_key;
    $this->backend = empty($backend) ? 'https://app.speechkit.io' : $backend;
		$this->news_site_id = $news_site_id;
		$this->voice_id = $voice_id;
  }

  private function generate_headers() {
    return array(
      'Authorization' => 'Token token='.$this->api_key,
      'Content-Type' => 'application/json'
    );
  }

	private function generate_default_fetch_options() {
		return array(
			'headers' => $this->generate_headers(),
			'blocking' => true,
			'timeout' => 60,
			'httpversion' => '1.1'
		);
	}

	private function generate_body_from_post($post_id) {
		$post = get_post($post_id);
		$post_meta = get_post_meta($post_id, 'speechkit_info', true);

		// Parse out all data
		$external_id = $post_id;
		$title = $post->post_title;
		$body = $post->post_content; // wp_strip_all_tags(apply_filters( 'the_content', $post->post_content ));
		// $summary = wp_strip_all_tags(apply_filters('the_excerpt', $post->post_content ));
		$external_url = get_permalink( $post_id );
		$published_at = $post->post_date_gmt;
		$author = get_the_author_meta( 'display_name', $post->post_author );
		$image_url = get_the_post_thumbnail_url( $post_id, 'thumbnail' );

		$body = array(
			'external_id' => $external_id,
			'title' => $title,
			'body' => $body,
			// 'summary' => $summary,
			'external_url' => $external_url,
			'published_at' => $published_at,
			'author' => $author,
			'image_url' => $image_url
		);

		if ($post_meta && $post_meta['media']) {
			$body['media_attributes'] = $post_meta['media'];
		} else {
			$body['media_attributes'] = array(
				array(
					'role' => 'body',
					'voice_id' => $this->voice_id
				)
			);
		}

		// if ($post_meta['media_attributes'])
		return json_encode($body);
	}

	private function generate_base_url() {
		return $this->backend . "/api/v1/news_sites/" . $this->news_site_id . "/";
	}

	public function get_article($post_id) {
		$url = $this->generate_base_url() . "articles/" . $post_id;
		return wp_remote_get( $url, $this->generate_default_fetch_options());
	}

	public function create_article($post_id) {
		$url = $this->generate_base_url() . "articles";

		return wp_remote_post( $url, array_merge(
			$this->generate_default_fetch_options(),
			array(
				'method' => 'POST',
				'body' => $this->generate_body_from_post($post_id)
			)
		));
	}

	public function update_article($post_id) {
		$url = $this->generate_base_url() . "articles/" . $post_id ;

		return wp_remote_post( $url, array_merge(
			$this->generate_default_fetch_options(),
			array(
				'method' => 'PUT',
				'body' => $this->generate_body_from_post($post_id)
			)
		));
	}

}
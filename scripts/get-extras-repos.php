<?php

	require('cli.php');

	class Repos extends CLI {

		public $api_url = 'https://api.github.com/orgs/habari-extras/repos';

		public $cache_file = 'repos.cache';

		public function get ( ) {

			// first, check for cached contents
			$cached = $this->cached( $this->cache_file );

			if ( $cached ) {
				$this->stdout( $cached );
				return;
			}

			// set the total number of pages to 1 for now - it'll get updated in the loop as we find out how many there really are
			$pages = 1;
			$i = 0;
			for ( $page = 1; $page <= $pages; $page++ ) {

				// get this page of items
				$result = $this->get_page( $page );

				// make sure we go all the way
				$pages = $result['last_page'];

				foreach ( $result['items'] as $repo ) {

					$i++;

					$json = json_encode( $repo );

					file_put_contents( $this->cache_file, $json . "\n", FILE_APPEND );

					$this->stdout( $json . "\n" );

				}

			}

		}

		protected function get_page ( $page = 1 ) {

			$url = $this->api_url . '?page=' . $page;

			$content = file_get_contents( $url );

			if ( $content === false ) {
				throw new Exception('Unable to get repo list');
			}

			$items = json_decode( $content );

			// parse out the next and last page links
			foreach ( $http_response_header as $header ) {
				if ( strpos( $header, 'Link: ' ) === 0 ) {

					preg_match_all( '/page=(\d+)/', $header, $matches );

					$next_page = $matches[1][0];
					$last_page = $matches[1][1];

				}
			}

			if ( !isset( $next_page ) || !isset( $last_page ) ) {
				throw new Exception( 'Unable to parse next and last pages from response' );
			}

			return array(
				'items' => $items,
				'next_page' => $next_page,
				'last_page' => $last_page,
			);

		}

	}


	$repos = new Repos();
	$repos->get();


?>
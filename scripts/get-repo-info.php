<?php

	require('cli.php');

	class RepoDescriptions extends CLI {

		public $repo_temp = 'repos/';

		public $cache_dir = 'descriptions/';

		public function __construct ( ) {

			if ( !is_dir( $this->repo_temp ) ) {
				mkdir( $this->repo_temp );
			}

			parent::__construct();

		}

		public function get ( ) {

			$input = $this->stdin();

			$repos = explode( "\n", $input );

			// remove the blank line at the end, basically
			$repos = array_filter( $repos );

			foreach ( $repos as $r ) {

				$repo = json_decode( $r );

				// check for cached info for this repo
				$cached = $this->cached( $this->cache_dir . '/' . md5( $repo->name ) . '.cache' );

				if ( $cached ) {
					$this->stdout( $cached );
					continue;
				}

				$this->clone_repo( $repo->name, $repo->clone_url );

				// look for xml info files
				$xml_files = glob( $this->repo_temp . '/' . $repo->name . '/{*.plugin.xml,theme.xml}', GLOB_BRACE );

				if ( count( $xml_files ) == 0 ) {
					$this->stderr( 'No XML files found for ' . $repo->name . "\n" );
					continue;
				}

				// iterate over all of the xml files we found until we get the info we want
				foreach ( $xml_files as $file ) {
					$info = $this->get_info( $file );

					// if we actually got info, break out of our loop
					if ( $info !== false ) {
						break;
					}
				}

				// if there wasn't enough info in any of the xml files
				if ( $info === false ) {
					$this->stderr( 'No info found in any XML files for ' . $repo->name . "\n" );
					continue;
				}

				// add the most basic repo info to the array, so we know what to clone next
				$info['clone_url'] = $repo->clone_url;
				$info['name'] = $repo->name;

				$json = json_encode( $info );

				// cache it
				file_put_contents( $this->cache_dir . '/' . md5( $repo->name ) . '.cache', $json . "\n", FILE_APPEND );

				// and simply output it, json encoded, so it can be used next
				$this->stdout( $json . "\n" );

			}

		}

		protected function get_info ( $xml_file ) {

			$info = array(
				'description' => null,
				'type' => null,
				'url' => null,
			);

			$xml = file_get_contents( $xml_file );

			// read it in with SimpleXML, which is horrible, but gets the job done
			$s = new SimpleXMLElement( $xml );

			// make sure it has a description
			if ( isset( $s->description ) && !empty( $s->description ) ) {
				$info['description'] = (string) $s->description;
			}
			else {
				// this file doesn't include what we want, so let's ignore it -- this will actually result in using the next file, if there is one
				return false;
			}

			// get the type
			$info['type'] = (string) $s['type'];

			// see if there is a url
			if ( isset( $s->url ) && !empty( $s->url ) ) {
				$info['url'] = (string)$s->url;
			}

			return $info;

		}

		protected function clone_repo ( $name, $url ) {

			$current_dir = getcwd();

			chdir( $this->repo_temp );

			// do we already have a repo of that name?
			if ( is_dir( $name ) ) {
				chdir( $name );
				$this->exec( 'git pull', null, false, true );
			}
			else {
				$this->exec( 'git clone ' . $url . ' ' . $name, null, false, true );
			}

			chdir( $current_dir );

		}

	}


	$descriptions = new RepoDescriptions();
	$descriptions->get();


?>
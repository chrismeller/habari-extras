<?php

	require('cli.php');

	class AddRepoSubmodule extends CLI {

		public $repo_dir = 'directory_repo';

		public function run ( ) {

			$input = $this->stdin();

			$repos = explode( "\n", $input );

			// remove the blank line at the end, basically
			$repos = array_filter( $repos );

			// before anything, pull upstream changes into our local repo
			$this->pull();

			foreach ( $repos as $r ) {

				$repo = json_decode( $r );

				switch ( strtolower( $repo->type ) )  {
					case 'plugin':
					case 'theme':
						$type = strtolower( $repo->type );
						break;

					default:
						$type = 'broken';
						break;
				}

				if ( $this->submodule_exists( $type, $repo->name ) ) {
					// we don't need to add it, just update the description
					$this->update_description( $type, $repo->name, $repo->description );

					$this->add_changes();

					$this->commit( 'Updated ' . $repo->name . ' ' . $type );
				}
				else {

					// first, add the submodule
					$this->add_submodule( $type, $repo->name, $repo->clone_url );

					// then update the description
					$this->update_description( $type, $repo->name, $repo->description );

					$this->add_changes();

					$this->commit( 'Added ' . $repo->name . ' ' . $type );

				}

			}

			$this->push();

		}

		protected function add_changes ( ) {

			$current_dir = getcwd();

			chdir( $this->repo_dir );

			$this->exec( 'git add *' );

			chdir( $current_dir );

		}

		protected function commit ( $message ) {

			$current_dir = getcwd();

			chdir( $this->repo_dir );

			$this->exec( 'git commit -m "' . $message . '"' );

			chdir( $current_dir );

		}

		protected function push ( ) {

			$current_dir = getcwd();

			chdir( $this->repo_dir );

			$this->exec( 'git push -u origin master' );

			chdir( $current_dir );

		}

		protected function pull ( ) {

			$current_dir = getcwd();

			chdir( $this->repo_dir );

			$this->exec( 'git pull origin master' );

			chdir( $current_dir );

		}

		protected function update_description ( $type, $name, $description ) {

			$current_dir = getcwd();

			chdir( $this->repo_dir );

			$filename = $type . '/' . $name . '.txt';

			file_put_contents( $filename, $description );

			chdir( $current_dir );

		}

		protected function submodule_exists ( $type, $name ) {

			$current_dir = getcwd();

			chdir( $this->repo_dir );

			$path = $type . '/' . $name;

			$result = $this->exec( 'git submodule status | grep ' . $path );

			// just one more sanity check, really
			if ( strpos( $result['output'], $path ) !== false ) {
				$result = true;
			}
			else {
				$result = false;
			}

			chdir( $current_dir );

			return $result;

		}

		protected function add_submodule ( $type, $name, $clone_url ) {

			$current_dir = getcwd();

			chdir( $this->repo_dir );

			$command = 'git submodule add ' . $clone_url . ' ' . $type . '/' . $name;

			$result = $this->exec( $command );

			chdir( $current_dir );

		}

	}

	$add_repo_submodule = new AddRepoSubmodule();
	$add_repo_submodule->run();

?>
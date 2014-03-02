<?php

	ini_set( 'display_errors', true );
	error_reporting(-1);

	date_default_timezone_set( 'UTC' );


	class CLI {

		public function __construct ( ) {



		}

		protected function stdin ( ) {

			return stream_get_contents( STDIN );

		}

		protected function stdout ( $contents ) {

			fwrite( STDOUT, $contents );

		}

		protected function stderr ( $contents ) {

			fwrite( STDERR, $contents );

		}

		protected function exec ( $command, $input = null, $output_stdout = true, $output_stderr = true ) {

			$descriptorspec = array(
				0 => array( 'pipe', 'rw' ),		// stdin
				1 => array( 'pipe', 'w' ), 		// stdout
				2 => array( 'pipe', 'w' ), 		// stderr
			);

			$process = proc_open( $command, $descriptorspec, $pipes );

			// if there is input, write it to stdin
			if ( $input != null ) {
				fwrite( $pipes[0], $input );
			}

			// then close it, there is nothing else to input
			fclose( $pipes[0] );

			// read the output pipes
			//$out = stream_get_contents( $pipes[1] );
			//$err = stream_get_contents( $pipes[2] );

			// the output streams can be long, so loop through reading them, saving them, and possibly outputting them
			$out = '';
			$err = '';
			while ( !feof( $pipes[1] ) ) {
				$line = fgets( $pipes[1], 4096 );

				$out .= $line;

				if ( $output_stdout ) {
					$this->stdout( $line );
				}
			}

			while ( !feof( $pipes[2] ) ) {
				$line = fgets( $pipes[2], 4096 );

				$err .= $line;

				if ( $output_stderr ) {
					$this->stderr( $line );
				}
			}

			// and close them
			fclose( $pipes[1] );
			fclose( $pipes[2] );

			// close the process and save the return value
			$return_value = proc_close( $process );

			// return everything
			return array(
				'input' => $input,
				'output' => $out,
				'error' => $err,
				'return' => $return_value,
			);

		}

		protected function cached ( $filename, $threshold = '12 hours' ) {

			$date = new DateTime( '-' . $threshold );
			$threshold = $date->format('U');

			// see if our cache file is recent enough
			if ( file_exists( $filename ) && filemtime( $filename ) >= $threshold ) {
				return file_get_contents( $filename );
			}
			else if ( file_exists( $filename ) ) {
				// clear out the cache by deleting the file
				unlink( $filename );
			}

		}

	}

?>
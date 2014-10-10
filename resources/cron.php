<?php
	/**
	 * Manage cron jobs though PHP & MySQL
	 *
	 * Reads functions to call from the cron table in a database,
	 * filters out tasks which are not scheduled to run again yet (using
	 * `last_ran` and `frequency` to determine the next scheduled execution),
	 * whether or not the function exists in a script in the cron/ directory,
	 * and finally executes each script, updating its `last_ran` before
	 * finishing.
	 *
	 * @author Chris Zuber <shgysk8zer0@gmail.com>
	 * @package core_shared
	 * @version 2014-09-18
	 * @copyright 2014, Chris Zuber
	 * @license http://opensource.org/licenses/GPL-3.0 GNU General Public License, version 3 (GPL-3.0)
	 * This program is free software; you can redistribute it and/or
	 * modify it under the terms of the GNU General Public License
	 * as published by the Free Software Foundation, either version 3
	 * of the License, or (at your option) any later version.
	 *
	 * This program is distributed in the hope that it will be useful,
	 * but WITHOUT ANY WARRANTY; without even the implied warranty of
	 * MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE. See the
	 * GNU General Public License for more details.
	 *
	 * You should have received a copy of the GNU General Public License
	 * along with this program.  If not, see <http://www.gnu.org/licenses/>.
	 *
	 * @uses \core\_pdo
	 * @param int $time
	 * @param array $jobs
	 * @param array $funcs
	 */

	namespace core\resources;

	class cron {
		private $time, $jobs, $funcs;

		/**
		 * The only public method in the class.
		 *
		 * The __construct method will load all jobs from the database,
		 * filter out those which are not yet scheduled to run,
		 * execute all scheduled tasks, and finally update the last_run
		 * time for all tasks which were successful.
		 *
		 * @param mixed $con [Variable containing database connection info]
		 */

		public function __construct($con = 'connect') {
			$pdo = \core\_pdo::load($con);
			if($pdo->connected) {
				$this->time = time();
				$this->jobs = array_filter(
					$this->all_jobs($pdo),
					[$this, 'check_scheduled']
				);
				if(is_array($this->jobs) and !empty($this->jobs)) {
					$this->get_functions();
					$this->call_cron();
					$this->update_last_ran($pdo);
				}
			}
		}

		/**
		 * Static method to get array of all functions included in all
		 * scripts in cron/ directory
		 *
		 * First, gets an array of all defined functions
		 * Then, load all scripts in cron/ directory
		 * Cron functions are the difference between the
		 * functions available before loading these scripts
		 * and the functions available after loading them.
		 *
		 * @return array    [Array of all function names in cron/ directory]
		 * @todo Consider skipping this and just using function_exists()
		 */

		public static function functions() {
			static $funcs = null;
			if(is_null($funcs)) {
				ob_start();
				$funcs = get_defined_functions()['user'];

				//Load all scripts from the cron/ directory
				array_map(
					function($file) {require_once($file);},
					glob(BASE . DIRECTORY_SEPARATOR . 'cron' . DIRECTORY_SEPARATOR . '*.php')
				);

				//Get user defined functions again. The difference is newly added functions
				$funcs = array_diff(
					get_defined_functions()['user'],
					$funcs
				);
				$funcs = array_unique($funcs);
				sort($funcs);
				ob_clean();
			}
			return $funcs;
		}

		/**
		 * Sets private $funcs to return from functions()
		 *
		 * @return void
		 */

		private function get_functions() {
			$this->funcs = static::functions();
		}


		/**
		 * Fetch all cron jobs from the database and save as $this->jobs
		 *
		 * @return void
		 */

		private function all_jobs(\core\_pdo &$pdo) {
			return $pdo->fetch_array("
				SELECT
					`function`,
					`arguments`,
					`frequency`,
					`last_ran`
				FROM `cron`
				ORDER BY `priority`
				ASC
			");
		}
		/**
		 * Checks whether $function is an available cron function or not
		 *
		 * @param  string  $function [Name of function]
		 * @return boolean
		 */

		private function has_function($function = null) {
			return in_array($function, $this->funcs);
		}

		/**
		 * Is this job scheduled to run again yet?
		 * Compares the last_ran time + frequency to the current time
		 *
		 * @param  stdClass $job [description]
		 * @return boolean
		 */

		private function check_scheduled($job) {
			return strtotime($job->frequency, strtotime($job->last_ran)) < $this->time;
		}


		/**
		 * Runs an array_filter on all jobs, which in turn
		 * calls each of the job's function.
		 *
		 * The filter is used to determine which jobs executed
		 *
		 * @return void
		 */

		private function call_cron() {
			array_filter($this->jobs, [$this, 'cron_filter']);
		}

		/**
		 * Where the cron jobs are actually executed.
		 *
		 * Runs the job's function, if available, passing along any
		 * optional arguments.
		 *
		 * If function is available, return the return from the function called,
		 * allowing each function to determine whether or not it was successful.
		 *
		 * Otherwise, return false.
		 *
		 * As this is ran in array_filter, all unsuccessful jobs will be
		 * removed from the $jobs array
		 *
		 * @return mixed
		 */

		private function cron_filter($job) {
			if($this->has_function($job->function)) {
				$args = explode(',', $job->arguments);
				array_walk($args, 'trim');
				return call_user_func($job->function, $args);
			}
			return false;
		}

		/**
		 * Updates `last_ran` column for all executed jobs
		 *
		 * @return void
		 */

		private function update_last_ran(\core\_pdo &$pdo) {
			$pdo->prepare("
				UPDATE `cron`
				SET `last_ran` = :time
				WHERE `function` = :function
			");

			//Update all jobs which completed with new last_run
			foreach($this->jobs as $job) {
				$pdo->bind([
					'time' => date('Y-m-d H:i', $this->time) . ':00',
					'function' => $job->function
				])->execute();
			}
		}
	}
?>

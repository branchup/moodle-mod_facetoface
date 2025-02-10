<?php
// This file is part of Moodle - http://moodle.org/
//
// Moodle is free software: you can redistribute it and/or modify
// it under the terms of the GNU General Public License as published by
// the Free Software Foundation, either version 3 of the License, or
// (at your option) any later version.
//
// Moodle is distributed in the hope that it will be useful,
// but WITHOUT ANY WARRANTY; without even the implied warranty of
// MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE.  See the
// GNU General Public License for more details.
//
// You should have received a copy of the GNU General Public License
// along with Moodle.  If not, see <http://www.gnu.org/licenses/>.

namespace mod_facetoface\reportbuilder\datasource;

use core_reportbuilder\datasource;
use core_reportbuilder\local\entities\course;
use core_reportbuilder\local\entities\user;
use mod_facetoface\local\reportbuilder\entities\facetoface as facetoface_entity;
use mod_facetoface\local\reportbuilder\entities\session;
use mod_facetoface\local\reportbuilder\entities\session_date;
use mod_facetoface\local\reportbuilder\entities\signup_live;

/**
 * Signups.
 *
 * @package    mod_facetoface
 * @copyright  2025 Murdoch University
 * @author     Frédéric Massart <fred@branchup.tech>
 * @license    http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */
class signups extends datasource {

    /**
     * Return user friendly name of the datasource
     *
     * @return string
     */
    public static function get_name(): string {
        return get_string('signups', 'mod_facetoface');
    }

    /**
     * Initialise report
     */
    protected function initialise(): void {
        $courseentity = new course();
        $facetofaceentity = new facetoface_entity();
        $sessionentity = new session();
        $sessiondateentity = new session_date();
        $signupentity = (new signup_live())->set_entity_name('signup');
        $userattendeeentity = (new user())
            ->set_entity_title(new \lang_string('attendee', 'mod_facetoface'));

        $coursealias = $courseentity->get_table_alias('course');
        $facetofacealias = $facetofaceentity->get_table_alias('facetoface');
        $sessionalias = $sessionentity->get_table_alias('facetoface_sessions');
        $sessiondatealias = $sessiondateentity->get_table_alias('facetoface_sessions_dates');
        $signupalias = $signupentity->get_table_alias('facetoface_signups');
        $userattendeealias = $userattendeeentity->get_table_alias('user');

        $this->set_main_table('facetoface', $facetofacealias);

        $this->add_entity($courseentity);
        $this->add_entity($facetofaceentity);
        $this->add_entity($sessionentity);
        $this->add_entity($sessiondateentity);
        $this->add_entity($signupentity);
        $this->add_entity($userattendeeentity);

        // Join the tables together.
        $this->add_join("JOIN {course} {$coursealias}
                           ON {$facetofacealias}.course = {$coursealias}.id");
        $this->add_join("LEFT JOIN {facetoface_sessions} {$sessionalias}
                                ON {$sessionalias}.facetoface = {$facetofacealias}.id");
        $this->add_join("LEFT JOIN {facetoface_sessions_dates} {$sessiondatealias}
                                ON {$sessiondatealias}.sessionid = {$sessionalias}.id");
        $this->add_join("LEFT JOIN {facetoface_signups} {$signupalias}
                                ON {$signupalias}.sessionid = {$sessionalias}.id");
        foreach ($signupentity->get_joins() as $join) {
            $this->add_join($join);
        }
        $this->add_join("LEFT JOIN {user} {$userattendeealias}
                                ON {$userattendeealias}.id = {$signupalias}.userid");

        // Add all columns from each entity.
        $this->add_all_from_entity($courseentity->get_entity_name());
        $this->add_all_from_entity($facetofaceentity->get_entity_name());
        $this->add_all_from_entity($sessionentity->get_entity_name());
        $this->add_all_from_entity($sessiondateentity->get_entity_name());
        $this->add_all_from_entity($signupentity->get_entity_name());
        $this->add_all_from_entity($userattendeeentity->get_entity_name());

        // // Add all filters from each entity.
        // $this->add_all_filters_from_entity($facetofaceentity->get_entity_name());
        // $this->add_all_filters_from_entity($sessionentity->get_entity_name());
        // $this->add_all_filters_from_entity($sessiondateentity->get_entity_name());
        // $this->add_all_filters_from_entity($signupentity->get_entity_name());
        // $this->add_all_filters_from_entity($signupstatusentity->get_entity_name());
        // $this->add_all_filters_from_entity($userattendeeentity->get_entity_name());

        // // Add all conditions from each entity.
        // $this->add_all_conditions_from_entity($facetofaceentity->get_entity_name());
        // $this->add_all_conditions_from_entity($sessionentity->get_entity_name());
        // $this->add_all_conditions_from_entity($sessiondateentity->get_entity_name());
        // $this->add_all_conditions_from_entity($signupentity->get_entity_name());
        // $this->add_all_conditions_from_entity($signupstatusentity->get_entity_name());
        // $this->add_all_conditions_from_entity($userattendeeentity->get_entity_name());
    }

    /**
     * Return the default columns.
     *
     * @return string[]
     */
    public function get_default_columns(): array {
        return [
            'facetoface:name',
            'session:capacity',
            'session_date:timestart',
            'session_date:timefinish',
            'user:fullname',
            'signup:status'
        ];
    }

    /**
     * Return the default filters.
     *
     * @return string[]
     */
    public function get_default_filters(): array {
        return [];
    }

    /**
     * Return the default conditions.
     *
     * @return string[]
     */
    public function get_default_conditions(): array {
        return [];
    }
}

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

namespace mod_facetoface\local\reportbuilder\entities;

use core_reportbuilder\local\entities\base;
use core_reportbuilder\local\filters\autocomplete;
use core_reportbuilder\local\filters\boolean_select;
use core_reportbuilder\local\filters\date;
use core_reportbuilder\local\filters\number;
use core_reportbuilder\local\filters\text;
use core_reportbuilder\local\report\column;
use core_reportbuilder\local\report\filter;
use lang_string;

defined('MOODLE_INTERNAL') || die();
require_once($CFG->dirroot . '/mod/facetoface/lib.php');

/**
 * Signup live.
 *
 * This is the combination of signups and signups_status except that it only
 * reads the latest status for each signup.
 *
 * @package    mod_facetoface
 * @copyright  2025 Murdoch University
 * @author     Frédéric Massart <fred@branchup.tech>
 * @license    http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */
class signup_live extends base {

    protected function get_default_entity_title(): lang_string {
        return new lang_string('signup', 'mod_facetoface');
    }

    protected function get_default_tables(): array {
        return ['facetoface_signups', 'facetoface_signups_status'];
    }

    public function initialise(): base {

        $signupsalias = $this->get_table_alias('facetoface_signups');
        $statusalias = $this->get_table_alias('facetoface_signups_status');

        $this->add_join("LEFT JOIN {facetoface_signups_status} {$statusalias}
                                ON {$statusalias}.signupid = {$signupsalias}.id
                               AND {$statusalias}.superceded = 0");

        $this->initialise_columns();

        foreach ($this->make_all_filters() as $filter) {
            $this->add_filter($filter);
            $this->add_condition($filter);
        }

        return $this;
    }

    /**
     * Initialise columns.
     *
     * @return self
     */
    protected function initialise_columns(): self {
        $signupstable = $this->get_table_alias('facetoface_signups');
        $statustable = $this->get_table_alias('facetoface_signups_status');

        $column = (new column('mailedreminder', new lang_string('mailedreminder', 'mod_facetoface'), $this->get_entity_name()))
            ->add_field("{$signupstable}.mailedreminder")
            ->add_joins($this->get_joins())
            ->set_type(column::TYPE_BOOLEAN);
        $this->add_column($column);

        $column = (new column('discountcode', new lang_string('discountcode', 'mod_facetoface'), $this->get_entity_name()))
            ->add_field("{$signupstable}.discountcode")
            ->add_joins($this->get_joins())
            ->set_type(column::TYPE_TEXT);
        $this->add_column($column);

        $column = (new column('status', new lang_string('status', 'mod_facetoface'), $this->get_entity_name()))
            ->add_field("{$statustable}.statuscode")
            ->add_joins($this->get_joins())
            ->set_type(column::TYPE_INTEGER)
            ->add_callback(static function(?int $value) {
                if ($value === MDL_F2F_STATUS_BOOKED) {
                    return get_string('status_booked', 'mod_facetoface');
                } else if ($value === MDL_F2F_STATUS_WAITLISTED) {
                    return get_string('status_waitlisted', 'mod_facetoface');
                } else if ($value === MDL_F2F_STATUS_REQUESTED) {
                    return get_string('status_requested', 'mod_facetoface');
                } else if ($value === MDL_F2F_STATUS_APPROVED) {
                    return get_string('status_approved', 'mod_facetoface');
                } else if ($value === MDL_F2F_STATUS_DECLINED) {
                    return get_string('status_declined', 'mod_facetoface');
                } else if ($value === MDL_F2F_STATUS_SESSION_CANCELLED) {
                    return get_string('status_session_cancelled', 'mod_facetoface');
                } else if ($value === MDL_F2F_STATUS_NO_SHOW) {
                    return get_string('status_no_show', 'mod_facetoface');
                } else if ($value === MDL_F2F_STATUS_PARTIALLY_ATTENDED) {
                    return get_string('status_partially_attended', 'mod_facetoface');
                } else if ($value === MDL_F2F_STATUS_FULLY_ATTENDED) {
                    return get_string('status_fully_attended', 'mod_facetoface');
                }
                return $value;
            });
        $this->add_column($column);

        $column = (new column('superceded', new lang_string('superceded', 'mod_facetoface'), $this->get_entity_name()))
            ->add_field("{$statustable}.superceded")
            ->add_joins($this->get_joins())
            ->set_type(column::TYPE_BOOLEAN);
        $this->add_column($column);

        $column = (new column('grade', new lang_string('gradenoun', 'core'), $this->get_entity_name()))
            ->add_field("{$statustable}.grade")
            ->add_joins($this->get_joins())
            ->set_type(column::TYPE_FLOAT)
            ->set_is_sortable(true);
        $this->add_column($column);

        $column = (new column('note', new lang_string('note', 'mod_facetoface'), $this->get_entity_name()))
            ->add_field("{$statustable}.note")
            ->add_joins($this->get_joins())
            ->set_type(column::TYPE_TEXT);
        $this->add_column($column);

        $column = (new column('timecreated', new lang_string('timecreated', 'core_reportbuilder'), $this->get_entity_name()))
            ->add_field("{$statustable}.timecreated")
            ->add_joins($this->get_joins())
            ->set_type(column::TYPE_TIMESTAMP)
            ->set_is_sortable(true);
        $this->add_column($column);

        return $this;
    }

    /**
     * Make all the filters.
     *
     * @return filter[]
     */
    protected function make_all_filters(): array {
        $signupstable = $this->get_table_alias('facetoface_signups');
        $statustable = $this->get_table_alias('facetoface_signups_status');
        $filters = [];

        $filters[] = (new filter(
            boolean_select::class,
            'mailedreminder',
            new lang_string('mailedreminder', 'mod_facetoface'),
            $this->get_entity_name(),
            "{$signupstable}.mailedreminder"
        ))->add_joins($this->get_joins());

        $filters[] = (new filter(
            text::class,
            'discountcode',
            new lang_string('discountcode', 'mod_facetoface'),
            $this->get_entity_name(),
            "{$signupstable}.discountcode"
        ))->add_joins($this->get_joins());

        $filters[] = (new filter(
            autocomplete::class,
            'status',
            new lang_string('status', 'mod_facetoface'),
            $this->get_entity_name(),
            "{$statustable}.statuscode"
        ))->set_options([
            MDL_F2F_STATUS_BOOKED => get_string('status_booked', 'mod_facetoface'),
            MDL_F2F_STATUS_WAITLISTED => get_string('status_waitlisted', 'mod_facetoface'),
            MDL_F2F_STATUS_REQUESTED => get_string('status_requested', 'mod_facetoface'),
            MDL_F2F_STATUS_APPROVED => get_string('status_approved', 'mod_facetoface'),
            MDL_F2F_STATUS_DECLINED => get_string('status_declined', 'mod_facetoface'),
            MDL_F2F_STATUS_SESSION_CANCELLED => get_string('status_session_cancelled', 'mod_facetoface'),
            MDL_F2F_STATUS_NO_SHOW => get_string('status_no_show', 'mod_facetoface'),
            MDL_F2F_STATUS_PARTIALLY_ATTENDED => get_string('status_partially_attended', 'mod_facetoface'),
            MDL_F2F_STATUS_FULLY_ATTENDED => get_string('status_fully_attended', 'mod_facetoface'),
        ])->add_joins($this->get_joins());

        $filters[] = (new filter(
            boolean_select::class,
            'superceded',
            new lang_string('superceded', 'mod_facetoface'),
            $this->get_entity_name(),
            "{$statustable}.superceded"
        ))->add_joins($this->get_joins());

        $filters[] = (new filter(
            number::class,
            'grade',
            new lang_string('gradenoun', 'core'),
            $this->get_entity_name(),
            "{$statustable}.grade"
        ))->add_joins($this->get_joins());

        $filters[] = (new filter(
            text::class,
            'note',
            new lang_string('note', 'mod_facetoface'),
            $this->get_entity_name(),
            "{$statustable}.note"
        ))->add_joins($this->get_joins());

        $filters[] = (new filter(
            date::class,
            'timecreated',
            new lang_string('timecreated', 'core_reportbuilder'),
            $this->get_entity_name(),
            "{$statustable}.timecreated"
        ))->add_joins($this->get_joins());

        return $filters;
    }
}

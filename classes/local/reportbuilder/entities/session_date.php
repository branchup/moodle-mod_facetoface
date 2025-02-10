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
use core_reportbuilder\local\filters\date;
use core_reportbuilder\local\report\column;
use core_reportbuilder\local\report\filter;
use lang_string;

/**
 * Entity.
 *
 * @package    mod_facetoface
 * @copyright  2025 Murdoch University
 * @author     Frédéric Massart <fred@branchup.tech>
 * @license    http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */
class session_date  extends base {

    protected function get_default_entity_title(): lang_string {
        return new lang_string('sessiondates', 'mod_facetoface');
    }

    protected function get_default_tables(): array {
        return ['facetoface_sessions_dates'];
    }

    public function initialise(): base {

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
        $table = $this->get_table_alias('facetoface_sessions_dates');

        $column = (new column('timestart', new lang_string('timestart', 'mod_facetoface'), $this->get_entity_name()))
            ->add_field("{$table}.timestart")
            ->add_joins($this->get_joins())
            ->set_type(column::TYPE_TIMESTAMP)
            ->set_is_sortable(true)
            ->add_callback(function($value) {
                return userdate($value);
            });
        $this->add_column($column);

        $column = (new column('timefinish', new lang_string('timefinish', 'mod_facetoface'), $this->get_entity_name()))
            ->add_field("{$table}.timefinish")
            ->add_joins($this->get_joins())
            ->set_type(column::TYPE_TIMESTAMP)
            ->set_is_sortable(true)
            ->add_callback(function($value) {
                return userdate($value);
            });
        $this->add_column($column);

        return $this;
    }

    /**
     * Make all the filters.
     *
     * @return filter[]
     */
    protected function make_all_filters(): array {
        $table = $this->get_table_alias('facetoface_sessions_dates');
        $filters = [];

        $filters[] = (new filter(
            date::class,
            'timestart',
            new lang_string('timestart', 'mod_facetoface'),
            $this->get_entity_name(),
            "{$table}.timestart"
        ))->add_joins($this->get_joins());

        $filters[] = (new filter(
            date::class,
            'timefinish',
            new lang_string('timefinish', 'mod_facetoface'),
            $this->get_entity_name(),
            "{$table}.timefinish"
        ))->add_joins($this->get_joins());

        return $filters;
    }

}

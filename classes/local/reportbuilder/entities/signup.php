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
use core_reportbuilder\local\filters\boolean_select;
use core_reportbuilder\local\filters\text;
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
class signup extends base {

    protected function get_default_entity_title(): lang_string {
        return new lang_string('signups', 'mod_facetoface');
    }

    protected function get_default_tables(): array {
        return ['facetoface_signups'];
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
        $table = $this->get_table_alias('facetoface_signups');

        $column = (new column('mailedreminder', new lang_string('mailedreminder', 'mod_facetoface'), $this->get_entity_name()))
            ->add_field("{$table}.mailedreminder")
            ->add_joins($this->get_joins())
            ->set_type(column::TYPE_BOOLEAN);
        $this->add_column($column);

        $column = (new column('discountcode', new lang_string('discountcode', 'mod_facetoface'), $this->get_entity_name()))
            ->add_field("{$table}.discountcode")
            ->add_joins($this->get_joins())
            ->set_type(column::TYPE_TEXT);
        $this->add_column($column);

        return $this;
    }

    /**
     * Make all the filters.
     *
     * @return filter[]
     */
    protected function make_all_filters(): array {
        $table = $this->get_table_alias('facetoface_signups');
        $filters = [];

        $filters[] = (new filter(
            boolean_select::class,
            'mailedreminder',
            new lang_string('mailedreminder', 'mod_facetoface'),
            $this->get_entity_name(),
            "{$table}.mailedreminder"
        ))->add_joins($this->get_joins());

        $filters[] = (new filter(
            text::class,
            'discountcode',
            new lang_string('discountcode', 'mod_facetoface'),
            $this->get_entity_name(),
            "{$table}.discountcode"
        ))->add_joins($this->get_joins());

        return $filters;
    }
}

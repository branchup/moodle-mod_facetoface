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
use core_reportbuilder\local\filters\date;
use core_reportbuilder\local\filters\number;
use core_reportbuilder\local\filters\text;
use core_reportbuilder\local\helpers\database;
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
class session  extends base {

    /** @var array */
    protected $customfields;

    protected function get_default_entity_title(): lang_string {
        return new lang_string('sessions', 'mod_facetoface');
    }

    protected function get_default_tables(): array {
        return ['facetoface_sessions', 'facetoface_session_field', 'facetoface_session_data'];
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
        global $DB;

        $table = $this->get_table_alias('facetoface_sessions');

        $column = (new column('capacity', new lang_string('capacity', 'mod_facetoface'), $this->get_entity_name()))
            ->add_field("{$table}.capacity")
            ->add_joins($this->get_joins())
            ->set_type(column::TYPE_INTEGER)
            ->set_is_sortable(true);
        $this->add_column($column);

        $column = (new column('allowoverbook', new lang_string('allowoverbook', 'mod_facetoface'), $this->get_entity_name()))
            ->add_field("{$table}.allowoverbook")
            ->add_joins($this->get_joins())
            ->set_type(column::TYPE_BOOLEAN);
        $this->add_column($column);

        $column = (new column('details', new lang_string('details', 'mod_facetoface'), $this->get_entity_name()))
            ->add_field("{$table}.details")
            ->add_joins($this->get_joins())
            ->set_type(column::TYPE_LONGTEXT);
        $this->add_column($column);

        $column = (new column('datetimeknown', new lang_string('sessiondatetimeknown', 'mod_facetoface'), $this->get_entity_name()))
            ->add_field("{$table}.datetimeknown")
            ->add_joins($this->get_joins())
            ->set_type(column::TYPE_BOOLEAN);
        $this->add_column($column);

        $column = (new column('duration', new lang_string('duration', 'mod_facetoface'), $this->get_entity_name()))
            ->add_field("{$table}.duration")
            ->add_joins($this->get_joins())
            ->set_type(column::TYPE_INTEGER)
            ->set_is_sortable(true);
        $this->add_column($column);

        $column = (new column('normalcost', new lang_string('normalcost', 'mod_facetoface'), $this->get_entity_name()))
            ->add_field("{$table}.normalcost")
            ->add_joins($this->get_joins())
            ->set_type(column::TYPE_INTEGER)
            ->set_is_sortable(true);
        $this->add_column($column);

        $column = (new column('discountcost', new lang_string('discountcost', 'mod_facetoface'), $this->get_entity_name()))
            ->add_field("{$table}.discountcost")
            ->add_joins($this->get_joins())
            ->set_type(column::TYPE_INTEGER)
            ->set_is_sortable(true);
        $this->add_column($column);

        $column = (new column('allowcancellations', new lang_string('allowcancellations', 'mod_facetoface'), $this->get_entity_name()))
            ->add_field("{$table}.allowcancellations")
            ->add_joins($this->get_joins())
            ->set_type(column::TYPE_BOOLEAN);
        $this->add_column($column);

        $column = (new column('timecreated', new lang_string('timecreated', 'core_reportbuilder'), $this->get_entity_name()))
            ->add_field("{$table}.timecreated")
            ->add_joins($this->get_joins())
            ->set_type(column::TYPE_TIMESTAMP)
            ->set_is_sortable(true);
        $this->add_column($column);

        $column = (new column('timemodified', new lang_string('timemodified', 'core_reportbuilder'), $this->get_entity_name()))
            ->add_field("{$table}.timemodified")
            ->add_joins($this->get_joins())
            ->set_type(column::TYPE_TIMESTAMP)
            ->set_is_sortable(true);
        $this->add_column($column);

        // Adding custom fields.
        foreach ($this->get_custom_fields() as $field) {
            $fieldalias = $field->alias;
            $column = (new column(
                'customfield_' . $field->shortname,
                new lang_string('customfieldcolumn', 'core_reportbuilder', $field->name),
                $this->get_entity_name()
            ))
                ->add_joins($this->get_joins())
                ->add_join($this->get_custom_field_join($field))
                ->add_field("{$fieldalias}.data")
                ->set_type(column::TYPE_TEXT);
            $this->add_column($column);
        }

        return $this;
    }

    /**
     * Make all the filters.
     *
     * @return filter[]
     */
    protected function make_all_filters(): array {
        $table = $this->get_table_alias('facetoface_sessions');
        $filters = [];

        $filters[] = (new filter(
            text::class,
            'details',
            new lang_string('details', 'mod_facetoface'),
            $this->get_entity_name(),
            "{$table}.details"
        ))->add_joins($this->get_joins());

        $filters[] = (new filter(
            number::class,
            'capacity',
            new lang_string('capacity', 'mod_facetoface'),
            $this->get_entity_name(),
            "{$table}.capacity"
        ))->add_joins($this->get_joins());

        $filters[] = (new filter(
            number::class,
            'normalcost',
            new lang_string('normalcost', 'mod_facetoface'),
            $this->get_entity_name(),
            "{$table}.normalcost"
        ))->add_joins($this->get_joins());

        $filters[] = (new filter(
            number::class,
            'duration',
            new lang_string('duration', 'mod_facetoface'),
            $this->get_entity_name(),
            "{$table}.duration"
        ))->add_joins($this->get_joins());

        $filters[] = (new filter(
            number::class,
            'discountcost',
            new lang_string('discountcost', 'mod_facetoface'),
            $this->get_entity_name(),
            "{$table}.discountcost"
        ))->add_joins($this->get_joins());

        $filters[] = (new filter(
            boolean_select::class,
            'allowoverbook',
            new lang_string('allowoverbook', 'mod_facetoface'),
            $this->get_entity_name(),
            "{$table}.allowoverbook"
        ))->add_joins($this->get_joins());

        $filters[] = (new filter(
            boolean_select::class,
            'datetimeknown',
            new lang_string('sessiondatetimeknown', 'mod_facetoface'),
            $this->get_entity_name(),
            "{$table}.datetimeknown"
        ))->add_joins($this->get_joins());

        $filters[] = (new filter(
            boolean_select::class,
            'allowcancellations',
            new lang_string('allowcancellations', 'mod_facetoface'),
            $this->get_entity_name(),
            "{$table}.allowcancellations"
        ))->add_joins($this->get_joins());

        $filters[] = (new filter(
            date::class,
            'timecreated',
            new lang_string('timecreated', 'core_reportbuilder'),
            $this->get_entity_name(),
            "{$table}.timecreated"
        ))->add_joins($this->get_joins());

        $filters[] = (new filter(
            date::class,
            'timemodified',
            new lang_string('timemodified', 'core_reportbuilder'),
            $this->get_entity_name(),
            "{$table}.timemodified"
        ))->add_joins($this->get_joins());

        // Adding custom fields.
        foreach ($this->customfields as $field) {
            $fieldalias = $field->alias;
            $filter = (new filter(
                text::class,
                'customfield_' . $field->shortname,
                new lang_string('customfieldcolumn', 'core_reportbuilder', $field->name),
                $this->get_entity_name(),
                "{$fieldalias}.data"
            ))
                ->add_joins($this->get_joins())
                ->add_join($this->get_custom_field_join($field));
            $filters[] = $filter;
        }

        return $filters;
    }

    /**
     * Get a customfield join.
     *
     * @param stdClass $field The custom field.
     * @return string
     */
    protected function get_custom_field_join($field): string {
        $table = $this->get_table_alias('facetoface_sessions');

        $fieldalias = $field->alias;
        $join = "LEFT JOIN {facetoface_session_data} {$fieldalias}
                        ON {$fieldalias}.sessionid = {$table}.id
                       AND {$fieldalias}.fieldid = {$field->id}";

        return $join;
    }

    /**
     * Get the custom fields.
     *
     * @return array
     */
    protected function get_custom_fields(): array {
        global $DB;
        if (!isset($this->customfields)) {
            $this->customfields = array_map(function($record) {
                return (object) [
                    'id' => $record->id,
                    'shortname' => $record->shortname,
                    'name' => $record->name,
                    'alias' => database::generate_alias(),
                ];
            }, $DB->get_records('facetoface_session_field', [], 'name ASC'));
        }
        return $this->customfields;
    }
}

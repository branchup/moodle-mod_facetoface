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
use core_reportbuilder\local\filters\select;
use core_reportbuilder\local\filters\text;
use core_reportbuilder\local\report\column;
use core_reportbuilder\local\report\filter;
use lang_string;

defined('MOODLE_INTERNAL') || die();
require_once($CFG->dirroot . '/mod/facetoface/lib.php');

/**
 * Entity.
 *
 * @package    mod_facetoface
 * @copyright  2025 Murdoch University
 * @author     Frédéric Massart <fred@branchup.tech>
 * @license    http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */
class facetoface  extends base {

    protected function get_default_entity_title(): lang_string {
        return new lang_string('pluginname', 'mod_facetoface');
    }

    protected function get_default_tables(): array {
        return ['facetoface'];
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
        $table = $this->get_table_alias('facetoface');

        $column = (new column('name', new lang_string('name', 'core'), $this->get_entity_name()))
            ->add_field("{$table}.name")
            ->add_joins($this->get_joins())
            ->set_type(column::TYPE_TEXT)
            ->set_is_sortable(true);
        $this->add_column($column);

        $column = (new column('shortname', new lang_string('shortname', 'mod_facetoface'), $this->get_entity_name()))
            ->add_field("{$table}.shortname")
            ->add_joins($this->get_joins())
            ->set_type(column::TYPE_TEXT)
            ->set_is_sortable(true);
        $this->add_column($column);

        $column = (new column('intro', new lang_string('description', 'mod_facetoface'), $this->get_entity_name()))
            ->add_field("{$table}.intro")
            ->add_joins($this->get_joins())
            ->set_type(column::TYPE_LONGTEXT);
        $this->add_column($column);

        $column = (new column('thirdparty', new lang_string('thirdpartyemailaddress', 'mod_facetoface'),
                $this->get_entity_name()))
            ->add_field("{$table}.thirdparty")
            ->add_joins($this->get_joins())
            ->set_type(column::TYPE_TEXT);
        $this->add_column($column);

        $column = (new column('thirdpartywaitlist', new lang_string('thirdpartywaitlist', 'mod_facetoface'),
                $this->get_entity_name()))
            ->add_field("{$table}.thirdpartywaitlist")
            ->add_joins($this->get_joins())
            ->set_type(column::TYPE_BOOLEAN);
        $this->add_column($column);

        $column = (new column('reminderperiod', new lang_string('reminderperiod', 'mod_facetoface'), $this->get_entity_name()))
            ->add_field("{$table}.reminderperiod")
            ->add_joins($this->get_joins())
            ->set_type(column::TYPE_INTEGER)
            ->set_is_sortable(true);
        $this->add_column($column);

        $column = (new column('showoncalendar', new lang_string('showoncalendar', 'mod_facetoface'), $this->get_entity_name()))
            ->add_field("{$table}.showoncalendar")
            ->add_joins($this->get_joins())
            ->set_type(column::TYPE_INTEGER)
            ->add_callback(static function (?int $value) {
                if ($value === F2F_CAL_NONE) {
                    return get_string('none', 'core');
                } else if ($value === F2F_CAL_COURSE) {
                    return get_string('course', 'core');
                } else if ($value === F2F_CAL_SITE) {
                    return get_string('site', 'core');
                }
                return $value;
            });
        $this->add_column($column);

        $column = (new column('approvalreqd', new lang_string('approvalreqd', 'mod_facetoface'), $this->get_entity_name()))
            ->add_field("{$table}.approvalreqd")
            ->add_joins($this->get_joins())
            ->set_type(column::TYPE_BOOLEAN);
        $this->add_column($column);

        $column = (new column('usercalentry', new lang_string('usercalentry', 'mod_facetoface'), $this->get_entity_name()))
            ->add_field("{$table}.usercalentry")
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

        $column = (new column('signuptype', new lang_string('signuptype', 'mod_facetoface'), $this->get_entity_name()))
            ->add_field("{$table}.signuptype")
            ->add_joins($this->get_joins())
            ->set_type(column::TYPE_INTEGER)
            ->add_callback(static function(?int $value) {
                if ($value === MOD_FACETOFACE_SIGNUP_SINGLE) {
                    return get_string('single', 'mod_facetoface');
                } else if ($value === MOD_FACETOFACE_SIGNUP_MULTIPLE) {
                    return get_string('multiple', 'mod_facetoface');
                }
                return $value;
            });
        $this->add_column($column);

        $column = (new column('multiplesignupmethod', new lang_string('multiplesignupmethod', 'mod_facetoface'), $this->get_entity_name()))
            ->add_field("{$table}.multiplesignupmethod")
            ->add_joins($this->get_joins())
            ->set_type(column::TYPE_INTEGER)
            ->add_callback(static function(?int $value) {
                if ($value === MOD_FACETOFACE_SIGNUP_MULTIPLE_PER_SESSION) {
                    return get_string('multiplesignuppersession', 'mod_facetoface');
                } else if ($value === MOD_FACETOFACE_SIGNUP_MULTIPLE_PER_ACTIVITY) {
                    return get_string('multiplesignupperactivity', 'mod_facetoface');
                }
                return $value;
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
        $table = $this->get_table_alias('facetoface');
        $filters = [];

        $filters[] = (new filter(
            text::class,
            'name',
            new lang_string('name', 'core'),
            $this->get_entity_name(),
            "{$table}.name"
        ))->add_joins($this->get_joins());

        $filters[] = (new filter(
            text::class,
            'shortname',
            new lang_string('shortname', 'mod_facetoface'),
            $this->get_entity_name(),
            "{$table}.shortname"
        ))->add_joins($this->get_joins());

        $filters[] = (new filter(
            text::class,
            'intro',
            new lang_string('description', 'mod_facetoface'),
            $this->get_entity_name(),
            "{$table}.intro"
        ))->add_joins($this->get_joins());

        $filters[] = (new filter(
            text::class,
            'thirdparty',
            new lang_string('thirdpartyemailaddress', 'mod_facetoface'),
            $this->get_entity_name(),
            "{$table}.thirdparty"
        ))->add_joins($this->get_joins());

        $filters[] = (new filter(
            boolean_select::class,
            'thirdpartywaitlist',
            new lang_string('thirdpartywaitlist', 'mod_facetoface'),
            $this->get_entity_name(),
            "{$table}.thirdpartywaitlist"
        ))->add_joins($this->get_joins());

        $filters[] = (new filter(
            select::class,
            'showoncalendar',
            new lang_string('showoncalendar', 'mod_facetoface'),
            $this->get_entity_name(),
            "{$table}.showoncalendar"
        ))->set_options([
            F2F_CAL_NONE => get_string('none', 'core'),
            F2F_CAL_COURSE => get_string('course', 'core'),
            F2F_CAL_SITE => get_string('site', 'core'),
        ])->add_joins($this->get_joins());

        $filters[] = (new filter(
            boolean_select::class,
            'approvalreqd',
            new lang_string('approvalreqd', 'mod_facetoface'),
            $this->get_entity_name(),
            "{$table}.approvalreqd"
        ))->add_joins($this->get_joins());

        $filters[] = (new filter(
            boolean_select::class,
            'usercalentry',
            new lang_string('usercalentry', 'mod_facetoface'),
            $this->get_entity_name(),
            "{$table}.usercalentry"
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

        $filters[] = (new filter(
            select::class,
            'signuptype',
            new lang_string('signuptype', 'mod_facetoface'),
            $this->get_entity_name(),
            "{$table}.signuptype"
        ))->set_options([
            MOD_FACETOFACE_SIGNUP_SINGLE => get_string('single', 'mod_facetoface'),
            MOD_FACETOFACE_SIGNUP_MULTIPLE => get_string('multiple', 'mod_facetoface'),
        ])->add_joins($this->get_joins());

        $filters[] = (new filter(
            select::class,
            'multiplesignupmethod',
            new lang_string('multiplesignupmethod', 'mod_facetoface'),
            $this->get_entity_name(),
            "{$table}.multiplesignupmethod"
        ))->set_options([
            MOD_FACETOFACE_SIGNUP_MULTIPLE_PER_SESSION => get_string('multiplesignuppersession', 'mod_facetoface'),
            MOD_FACETOFACE_SIGNUP_MULTIPLE_PER_ACTIVITY => get_string('multiplesignupperactivity', 'mod_facetoface'),
        ])->add_joins($this->get_joins());

        $filters[] = (new filter(
            number::class,
            'reminderperiod',
            new lang_string('reminderperiod', 'mod_facetoface'),
            $this->get_entity_name(),
            "{$table}.reminderperiod"
        ))->add_joins($this->get_joins());

        return $filters;
    }

}

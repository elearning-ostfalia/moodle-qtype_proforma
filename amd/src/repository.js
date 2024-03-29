// This file is part of ProFormA Question Type for Moodle
//
// ProFormA Question Type for Moodle is free software:
// you can redistribute it and/or modify
// it under the terms of the GNU General Public License as published by
// the Free Software Foundation, either version 3 of the License, or
// (at your option) any later version.
//
// ProFormA Question Type for Moodle is distributed in the hope that it will be useful,
// but WITHOUT ANY WARRANTY; without even the implied warranty of
// MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE.  See the
// GNU General Public License for more details.
//
// You should have received a copy of the GNU General Public License
// along with ProFormA Question Type for Moodle.
// If not, see <http://www.gnu.org/licenses/>.

/**
 * Ajax functions for ProFormA question type.
 *
 * @package    qtype
 * @subpackage proforma
 * @copyright  2023 Ostfalia Hochschule fuer angewandte Wissenschaften
 * @license    http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 * @author     K.Borm <k.borm[at]ostfalia.de>
 */

import Ajax from 'core/ajax';

export const downloadTask = (itemid
) => Ajax.call([{
    methodname: 'qtype_proforma_get_task_url',
    args: {
        itemid
    },
}])[0];


export const getJunitVersions = (
) => Ajax.call([{
    methodname: 'qtype_proforma_get_junit_versions',
    args: {
    },
}])[0];


export const getCheckstyleVersions = (
) => Ajax.call([{
    methodname: 'qtype_proforma_get_checkstyle_versions',
    args: {
    },
}])[0];

// TODO
export const getJavaVersions = (
) => Ajax.call([{
    methodname: 'qtype_proforma_get_java_versions',
    args: {
    },
}])[0];



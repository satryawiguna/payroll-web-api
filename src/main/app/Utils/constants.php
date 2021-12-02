<?php
/*
 * Copyright (c) 2021 All Rights Reserved.
 *
 * Unauthorized copying of this file, via any medium is strictly prohibited.
 * Proprietary and confidential.
 *
 * Written by:
 *   - Satrya Wiguna <satrya@freshcms.net>
 */

defined('DEFAULT_PER_PAGE') || define('DEFAULT_PER_PAGE', 15);

defined('BOT') || define('BOT', '1000-01-01');
defined('EOT') || define('EOT', '9000-12-31');

defined('SB_PREFIX') || define('SB_PREFIX', 'smartbiz.sb8669_');

defined('SEVERITY_INFO') || define('SEVERITY_INFO', 'I');
defined('SEVERITY_WARNING') || define('SEVERITY_WARNING', 'W');
defined('SEVERITY_ERROR') || define('SEVERITY_ERROR', 'E');

defined('PROCESS_TYPE_PAYROLL') || define('PROCESS_TYPE_PAYROLL', 'P');
defined('PROCESS_TYPE_RETROPAY') || define('PROCESS_TYPE_RETROPAY', 'R');

defined('PROCESS_STATUS_NEW') || define('PROCESS_STATUS_NEW', 'N');
defined('PROCESS_STATUS_SUCCESS') || define('PROCESS_STATUS_SUCCESS', 'S');
defined('PROCESS_STATUS_WARNING') || define('PROCESS_STATUS_WARNING', 'W');
defined('PROCESS_STATUS_FAILED') || define('PROCESS_STATUS_FAILED', 'F');

defined('LAST_ENTRY_TERMINATION') || define('LAST_ENTRY_TERMINATION', 'T');
defined('LAST_ENTRY_STANDARD_PROCESS') || define('LAST_ENTRY_STANDARD_PROCESS', 'S');
defined('LAST_ENTRY_FINAL_CLOSE') || define('LAST_ENTRY_FINAL_CLOSE', 'F');

defined('FORMULA_CATEGORY_PAYROLL') || define('FORMULA_CATEGORY_PAYROLL', 'P');
defined('FORMULA_CATEGORY_ATTENDANCE') || define('FORMULA_CATEGORY_ATTENDANCE', 'A');
defined('FORMULA_CATEGORY_SKIP_FORMULA') || define('FORMULA_CATEGORY_SKIP_FORMULA', 'X');

defined('FORMULA_TYPE_STORED_PROCEDURE') || define('FORMULA_TYPE_STORED_PROCEDURE', 'SP');
defined('FORMULA_TYPE_SIMPLE_FORMULA') || define('FORMULA_TYPE_SIMPLE_FORMULA', 'FX');

defined('INPUT_VALUE_AMOUNT') || define('INPUT_VALUE_AMOUNT', 'AMOUNT');
defined('INPUT_VALUE_PAY_VALUE') || define('INPUT_VALUE_PAY_VALUE', 'PAY_VALUE');
defined('INPUT_VALUE_DAY') || define('INPUT_VALUE_DAY', 'DAY');
defined('INPUT_VALUE_HOUR') || define('INPUT_VALUE_HOUR', 'HOUR');
defined('INPUT_VALUE_RATE') || define('INPUT_VALUE_RATE', 'RATE');
defined('INPUT_VALUE_COUNT') || define('INPUT_VALUE_COUNT', 'COUNT');
defined('INPUT_VALUE_CHILD_COUNT') || define('INPUT_VALUE_CHILD_COUNT', 'CHILD_COUNT');
defined('INPUT_VALUE_DESCRIPTION') || define('INPUT_VALUE_DESCRIPTION', 'DESCRIPTION');

defined('PAYSLIP_GROUP_EARNINGS') || define('PAYSLIP_GROUP_EARNINGS', '+');
defined('PAYSLIP_GROUP_DEDUCTIONS') || define('PAYSLIP_GROUP_DEDUCTIONS', '+');

/*
 * Copyright (c) 2021 All Rights Reserved.
 *
 * Unauthorized copying of this file, via any medium is strictly prohibited.
 * Proprietary and confidential.
 *
 * Written by:
 *   - Satrya Wiguna <satrya@freshcms.net>
 */

insert ignore into db_formula_list(formula_name, formula_category, parameters)
values
('sp_pay_basic', 'P', '[
    {"name":"_process_id"},
    {"name":"_per_process_id"},
    {"name":"_effective"},
    {"name":"_amount"},
    {"name":"_pay_value", "out": true}
]'),
('sp_pay_attendance', 'P', '[
    {"name":"_process_id"},
    {"name":"_per_process_id"},
    {"name":"_effective"},
    {"name":"_amount"},
    {"name":"_day", "out": true},
    {"name":"_pay_value", "out": true}
]'),
('sp_pay_family', 'P', '[
    {"name":"_process_id"},
    {"name":"_per_process_id"},
    {"name":"_effective"},
    {"name":"_amount"},
    {"name":"_child_count", "out": true},
    {"name":"_pay_value", "out": true}
]'),
('sp_pay_overtime', 'P', '[
    {"name":"_process_id"},
    {"name":"_per_process_id"},
    {"name":"_effective"},
    {"name":"_hour_1_5x", "out": true},
    {"name":"_hour_2x", "out": true},
    {"name":"_hour_3x", "out": true},
    {"name":"_hour_4x", "out": true},
    {"name":"_overtime_1_5x", "out": true},
    {"name":"_overtime_2x", "out": true},
    {"name":"_overtime_3x", "out": true},
    {"name":"_overtime_4x", "out": true}
]'),
('sp_pay_holiday', 'P', '[
    {"name":"_process_id"},
    {"name":"_per_process_id"},
    {"name":"_effective"},
    {"name":"description", "out": true},
    {"name":"_pay_value", "out": true}
]'),
('sp_pay_severance', 'P', '[
    {"name":"_process_id"},
    {"name":"_per_process_id"},
    {"name":"_effective"},
    {"name":"description", "out": true},
    {"name":"_pay_value", "out": true}
]'),
('sp_pay_astek', 'P', '[
    {"name":"_process_id"},
    {"name":"_per_process_id"},
    {"name":"_effective"},
    {"name":"rate_jkn"},
    {"name":"rate_jht"},
    {"name":"rate_jkk"},
    {"name":"rate_jk"},
    {"name":"rate_jp"},
    {"name":"value_jkn", "out": true},
    {"name":"value_jht", "out": true},
    {"name":"value_jkk", "out": true},
    {"name":"value_jk", "out": true},
    {"name":"value_jp", "out": true}
]'),
('sp_pay_tax', 'P', '[
    {"name":"_process_id"},
    {"name":"_per_process_id"},
    {"name":"_effective"},
    {"name":"_pay_value", "out": true}
]');

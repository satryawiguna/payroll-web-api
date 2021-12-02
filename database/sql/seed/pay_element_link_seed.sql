/*
 * Copyright (c) 2021 All Rights Reserved.
 *
 * Unauthorized copying of this file, via any medium is strictly prohibited.
 * Proprietary and confidential.
 *
 * Written by:
 *   - Satrya Wiguna <satrya@freshcms.net>
 */

insert ignore into pay_element_link(
    link_id, company_id, element_id,
    office_id, location_id, department_id, project_id, position_id, grade_id, pay_group_id, people_group,
    employee_category, employee_id
)
values
('BASIC_SAL', 1, 'BASIC_SAL', null, 5, null, null, null, null, null, null, null, null),
('FIXED_ALW', 1, 'FIXED_ALW', null, 5, null, null, null, null, null, null, null, null),
('FUNC_ALW', 1, 'FUNC_ALW', null, 5, null, null, null, null, null, null, null, null),
('FAMILY_ALW', 1, 'FAMILY_ALW', null, 5, null, null, null, null, null, null, null, null),

('OT', 1, 'OT', null, null, null, null, null, null, null, null, null, null),
('HOLIDAY_ALW', 1, 'HOLIDAY_ALW', null, null, null, null, null, null, null, null, null, null),
('SEV_ALW', 1, 'SEV_ALW', null, null, null, null, null, null, null, null, null, null),
('LOAN', 1, 'LOAN', null, null, null, null, null, null, null, null, null, null),
('ASTEK', 1, 'ASTEK', null, null, null, null, null, null, null, null, null, null),
('TAX', 1, 'TAX', null, null, null, null, null, null, null, null, null, null);

insert ignore into pay_element_link_value(value_id, company_id, link_id, input_value_id, value, description)
values
('FIXED_ALW_A', 1, 'FIXED_ALW', 'FIXED_ALW_A', '100000', null),
('FIXED_ALW_D', 1, 'FIXED_ALW', 'FIXED_ALW_D', '20', null),
('FUNC_ALW_P', 1, 'FUNC_ALW', 'FUNC_ALW_P', '150000', null),
('ATTENDANCE_A', 1, 'ATTENDANCE', 'ATTENDANCE_A', '200000', null),
('FAMILY_ALW_A', 1, 'FAMILY_ALW', 'FAMILY_ALW_A', '250000', null);

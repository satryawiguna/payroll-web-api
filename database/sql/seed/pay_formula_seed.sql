/*
 * Copyright (c) 2021 All Rights Reserved.
 *
 * Unauthorized copying of this file, via any medium is strictly prohibited.
 * Proprietary and confidential.
 *
 * Written by:
 *   - Satrya Wiguna <satrya@freshcms.net>
 */

insert ignore into pay_formula(formula_id, company_id, formula_name, element_id, formula_type, formula_def, description)
values
('BASIC_SAL', 1, 'Gaji Pokok', 'BASIC_SAL', 'SP', 'sp_pay_basic', null),
('FIXED_ALW', 1, 'Tunjanan Tetap', 'FIXED_ALW', 'FX', null, null),
('ATTENDANCE', 1, 'Kehadiran', 'ATTENDANCE', 'SP', 'sp_pay_attendance', null),
('FAMILY_ALW', 1, 'Tunjangan Keluarga', 'FAMILY_ALW', 'SP', 'sp_pay_family', null),
('OT', 1, 'Lembur', 'OT', 'SP', 'sp_pay_overtime', null),
('HOLIDAY_ALW', 1, 'Tunjangan Hari Raya', 'HOLIDAY_ALW', 'SP', 'sp_pay_holiday', null),
('SEV_ALW', 1, 'Pesangon', 'SEV_ALW', 'SP', 'sp_pay_severance', null),
('ASTEK', 1, 'Astek', 'ASTEK', 'SP', 'sp_pay_astek', null),
('TAX', 1, 'PPh 21', 'TAX', 'SP', 'sp_pay_tax', null);

insert ignore into pay_formula_result(result_id, company_id, formula_id, result_code, formula_expr, element_id, input_value_id)
values
('BASIC_SAL_P', 1, 'BASIC_SAL', 'PAY_VALUE', null, 'BASIC_SAL', 'BASIC_SAL_P'),

('FIXED_ALW_P', 1, 'FIXED_ALW', 'PAY_VALUE', 'fixed_allow.amount * fixed_allow.day', 'FIXED_ALW', 'FIXED_ALW_P'),

('ATTENDANCE_D', 1, 'ATTENDANCE', 'DAY', null, 'ATTENDANCE', 'ATTENDANCE_D'),
('ATTENDANCE_P', 1, 'ATTENDANCE', 'PAY_VALUE', null, 'ATTENDANCE', 'ATTENDANCE_P'),

('FAMILY_ALW_C', 1, 'FAMILY_ALW', 'CHILD_COUNT', null, 'FAMILY_ALW', 'FAMILY_ALW_C'),
('FAMILY_ALW_P', 1, 'FAMILY_ALW', 'PAY_VALUE', null, 'FAMILY_ALW', 'FAMILY_ALW_P'),

('OT_H1_5X', 1, 'OT', 'HOUR_1_5X', null, 'OT', 'OT_H1_5X'),
('OT_H2X', 1, 'OT', 'HOUR_2X', null, 'OT', 'OT_H2X'),
('OT_H3X', 1, 'OT', 'HOUR_3X', null, 'OT', 'OT_H3X'),
('OT_H4X', 1, 'OT', 'HOUR_4X', null, 'OT', 'OT_H4X'),
('OT_1_5X_H', 1, 'OT', 'HOUR_1_5X', null, 'OT_1_5X', 'OT_1_5X_H'),
('OT_2X_H', 1, 'OT', 'HOUR_2X', null, 'OT_2X', 'OT_2X_H'),
('OT_3X_H', 1, 'OT', 'HOUR_3X', null, 'OT_3X', 'OT_3X_H'),
('OT_4X_H', 1, 'OT', 'HOUR_4X', null, 'OT_4X', 'OT_4X_H'),
('OT_1_5X_P', 1, 'OT', 'OVERTIME_1_5X', null, 'OT_1_5X', 'OT_1_5X_P'),
('OT_2X_P', 1, 'OT', 'OVERTIME_2X', null, 'OT_2X', 'OT_2X_P'),
('OT_3X_P', 1, 'OT', 'OVERTIME_3X', null, 'OT_3X', 'OT_3X_P'),
('OT_4X_P', 1, 'OT', 'OVERTIME_4X', null, 'OT_4X', 'OT_4X_P'),

('HOLIDAY_ALW_D', 1, 'HOLIDAY_ALW', 'DESCRIPTION', null, 'HOLIDAY_ALW', 'HOLIDAY_ALW_D'),
('HOLIDAY_ALW_P', 1, 'HOLIDAY_ALW', 'PAY_VALUE', null, 'HOLIDAY_ALW', 'HOLIDAY_ALW_P'),

('SEV_ALW_D', 1, 'SEV_ALW', 'DESCRIPTION', null, 'SEV_ALW', 'SEV_ALW_D'),
('SEV_ALW_P', 1, 'SEV_ALW', 'PAY_VALUE', null, 'SEV_ALW', 'SEV_ALW_P'),

('JKN_R', 1, 'ASTEK', 'RATE_JKN', null, 'JKN', 'JKN_R'),
('JKN_P', 1, 'ASTEK', 'VALUE_JKN', null, 'JKN', 'JKN_P'),
('JHT_R', 1, 'ASTEK', 'RATE_JHT', null, 'JHT', 'JHT_R'),
('JHT_P', 1, 'ASTEK', 'VALUE_JHT', null, 'JHT', 'JHT_P'),
('JKK_R', 1, 'ASTEK', 'RATE_JKK', null, 'JKK', 'JKK_R'),
('JKK_P', 1, 'ASTEK', 'VALUE_JKK', null, 'JKK', 'JKK_P'),
('JK_R', 1, 'ASTEK', 'RATE_JK', null, 'JK', 'JK_R'),
('JK_P', 1, 'ASTEK', 'VALUE_JK', null, 'JK', 'JK_P'),
('JP_R', 1, 'ASTEK', 'RATE_JP', null, 'JP', 'JP_R'),
('JP_P', 1, 'ASTEK', 'VALUE_JP', null, 'JP', 'JP_P'),

('TAX_P', 1, 'TAX', 'PAY_VALUE', null, 'TAX', 'TAX_P');

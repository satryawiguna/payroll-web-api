/*
 * Copyright (c) 2021 All Rights Reserved.
 *
 * Unauthorized copying of this file, via any medium is strictly prohibited.
 * Proprietary and confidential.
 *
 * Written by:
 *   - Satrya Wiguna <satrya@freshcms.net>
 */

insert ignore into pay_element_classification(classification_id, company_id, classification_name, default_priority, description)
values
('REG_GROSS', null, 'Regular Gross Earnings', 1000, null),
('DIRECT_PAY', null, 'Direect Payments', 2000, null),
('ASTEK', null, 'Astek Deductions', 3000, null),
('TAX', null, 'Tax Deductions', 4000, null),
('VOLUNT', null, 'Voluntary Deductions', 2000, null);


insert ignore into pay_element(element_id, company_id, element_code, element_name, classification_id, last_entry_type,
                               processing_priority, is_recurring, is_once_per_period)
values
('BASIC_SAL', null, 'BASIC_SALARY', 'Gaji Pokok', 'REG_GROSS', 'T', 1010, 1, 1),
('FIXED_ALW', 1, 'FIXED_ALW', 'Tunjangan Tetap', 'REG_GROSS', 'T', 1020, 1, 1),
('FUNC_ALW', 1, 'FUNC_ALW', 'Tunjangan Fungsional', 'REG_GROSS', 'T', 2010, 1, 1),
('ATTENDANCE', 1, 'ATTENDANCE', 'Tunjangan Kehadiran', 'REG_GROSS', 'T', 2020, 1, 1),
('FAMILY_ALW', 1, 'FAMILY_ALW', 'Tunjangan Keluarga', 'REG_GROSS', 'T', 2030, 1, 1),
('OT', 1, 'OVERTIME', 'Tunjangan Lembur', 'REG_GROSS', 'T', 2040, 1, 1),
('OT_1_5X', 1, 'OVERTIME_1_5X', 'Lembur 1.5x', 'REG_GROSS', 'T', 2041, 1, 1),
('OT_2X', 1, 'OVERTIME_2X', 'Lembur 2x', 'REG_GROSS', 'T', 2042, 1, 1),
('OT_3X', 1, 'OVERTIME_3X', 'Lembur 3x', 'REG_GROSS', 'T', 2043, 1, 1),
('OT_4X', 1, 'OVERTIME_4X', 'Lembur 4x', 'REG_GROSS', 'T', 2044, 1, 1),
('HOLIDAY_ALW', 1, 'HOLIDAY_ALW', 'Tunjangan Hari Raya', 'REG_GROSS', 'T', 2050, 1, 1),
('SEV_ALW', 1, 'SEV_ALW', 'Pesangon', 'REG_GROSS', 'T', 2060, 1, 1),
('LOAN', 1, 'LOAN', 'Pinjaman', 'DIRECT_PAY', 'T', 2070, 0, 1),
('ASTEK', 1, 'ASTEK', 'Astek', 'ASTEK', 'T', 2080, 1, 1),
('JKN', 1, 'JKN', 'Jaminan Kesehatan Nasional (JKN)', 'ASTEK', 'T', 2081, 1, 1),
('JHT', 1, 'JHT', 'Jaminan Hari Tua (JHT)', 'ASTEK', 'T', 2082, 1, 1),
('JKK', 1, 'JKK', 'Jaminan Kecelakaan Kerja (JKK)', 'ASTEK', 'T', 2083, 1, 1),
('JK', 1, 'JK', 'Jaminan Kematian (JK)', 'ASTEK', 'T', 2084, 1, 1),
('JP', 1, 'JP', 'Jaminan Pensiun (JP)', 'ASTEK', 'T', 2085, 1, 1),
('TAX', 1, 'TAX', 'PPH 21', 'TAX', 'T', 2090, 1, 1),

('R_BASIC_SAL', null, 'R_BASIC_SAL', 'Rapel Gaji Pokok', 'REG_GROSS', 'T', 1010, 0, 1),
('R_FIXED_ALW', 1, 'R_FIXED_ALW', 'Rapel Tunjangan Tetap', 'REG_GROSS', 'T', 1020, 0, 1),
('R_FUNC_ALW', 1, 'R_FUNC_ALW', 'Rapel Tunjangan Fungsional', 'REG_GROSS', 'T', 2010, 0, 1),
('R_FAMILY_ALW', 1, 'R_FAMILY_ALW', 'Rapel Tunjangan Keluarga', 'REG_GROSS', 'T', 2030, 0, 1),
('R_OT_1_5X', 1, 'R_OT_1_5X', 'Rapel Lembur 1.5x', 'REG_GROSS', 'T', 2041, 0, 1),
('R_OT_2X', 1, 'R_OT_2X', 'Rapel Lembur 2x', 'REG_GROSS', 'T', 2042, 0, 1),
('R_OT_3X', 1, 'R_OT_3X', 'Rapel Lembur 3x', 'REG_GROSS', 'T', 2043, 0, 1),
('R_OT_4X', 1, 'R_OT_4X', 'Rapel Lembur 4x', 'REG_GROSS', 'T', 2044, 0, 1);

update pay_element set retro_element_id = 'R_BASIC_SAL' where element_id = 'BASIC_SAL';
update pay_element set retro_element_id = 'R_FIXED_ALW' where element_id = 'FIXED_ALW';
update pay_element set retro_element_id = 'R_FUNC_ALW' where element_id = 'FUNC_ALW';
update pay_element set retro_element_id = 'R_FAMILY_ALW' where element_id = 'FAMILY_ALW';
update pay_element set retro_element_id = 'R_OT_1_5X' where element_id = 'OT_1_5X';
update pay_element set retro_element_id = 'R_OT_2X' where element_id = 'OT_2X';
update pay_element set retro_element_id = 'R_OT_3X' where element_id = 'OT_3X';
update pay_element set retro_element_id = 'R_OT_4X' where element_id = 'OT_4X';


insert ignore into pay_input_value(input_value_id, company_id, element_id, value_code, value_name, default_value, data_type)
values
('BASIC_SAL_A', null, 'BASIC_SAL', 'AMOUNT', 'Amount', null, 'N'),
('BASIC_SAL_P', null, 'BASIC_SAL', 'PAY_VALUE', 'Pay Value', null, 'N'),

('FIXED_ALW_A', 1, 'FIXED_ALW', 'AMOUNT', 'Amount', null, 'N'),
('FIXED_ALW_D', 1, 'FIXED_ALW', 'DAY', 'Day', null, 'N'),
('FIXED_ALW_P', 1, 'FIXED_ALW', 'PAY_VALUE', 'Pay Value', null, 'N'),

('FUNC_ALW_P', 1, 'FUNC_ALW', 'PAY_VALUE', 'Pay Value', null, 'N'),

('ATTENDANCE_A', 1, 'ATTENDANCE', 'AMOUNT', 'Amount', null, 'N'),
('ATTENDANCE_D', 1, 'ATTENDANCE', 'DAY', 'Day', null, 'N'),
('ATTENDANCE_P', 1, 'ATTENDANCE', 'PAY_VALUE', 'Pay Value', null, 'N'),

('FAMILY_ALW_A', 1, 'FAMILY_ALW', 'AMOUNT', 'Amount', null, 'N'),
('FAMILY_ALW_C', 1, 'FAMILY_ALW', 'CHILD_COUNT', 'Child Count', null, 'N'),
('FAMILY_ALW_P', 1, 'FAMILY_ALW', 'PAY_VALUE', 'Pay Value', null, 'N'),

('OT_H1_5X', 1, 'OT', 'HOUR_1_5X', 'Hour 1.5x', null, 'N'),
('OT_H2X', 1, 'OT', 'HOUR_2X', 'Hour 2x', null, 'N'),
('OT_H3X', 1, 'OT', 'HOUR_3X', 'Hour 3x', null, 'N'),
('OT_H4X', 1, 'OT', 'HOUR_4X', 'Hour 4x', null, 'N'),

('OT_1_5X_H', 1, 'OT_1_5X', 'HOUR', 'Hour', null, 'N'),
('OT_1_5X_P', 1, 'OT_1_5X', 'PAY_VALUE', 'Pay Value', null, 'N'),

('OT_2X_H', 1, 'OT_2X', 'HOUR', 'Hour', null, 'N'),
('OT_2X_P', 1, 'OT_2X', 'PAY_VALUE', 'Pay Value', null, 'N'),

('OT_3X_H', 1, 'OT_3X', 'HOUR', 'Hour', null, 'N'),
('OT_3X_P', 1, 'OT_3X', 'PAY_VALUE', 'Pay Value', null, 'N'),

('OT_4X_H', 1, 'OT_4X', 'HOUR', 'Hour', null, 'N'),
('OT_4X_P', 1, 'OT_4X', 'PAY_VALUE', 'Pay Value', null, 'N'),

('HOLIDAY_ALW_D', 1, 'HOLIDAY_ALW', 'DESCRIPTION', 'Description', null, 'C'),
('HOLIDAY_ALW_P', 1, 'HOLIDAY_ALW', 'PAY_VALUE', 'Pay Value', null, 'N'),

('SEV_ALW_D', 1, 'SEV_ALW', 'DESCRIPTION', 'Description', null, 'C'),
('SEV_ALW_P', 1, 'SEV_ALW', 'PAY_VALUE', 'Pay Value', null, 'N'),

('LOAN_D', 1, 'LOAN', 'DESCRIPTION', 'Description', null, 'C'),
('LOAN_P', 1, 'LOAN', 'PAY_VALUE', 'Pay Value', null, 'N'),

('ASTEK_JKN', 1, 'ASTEK', 'RATE_JKN', 'Rate JKN', '1', 'N'),
('ASTEK_JHT', 1, 'ASTEK', 'RATE_JHT', 'Rate JHT', '2', 'N'),
('ASTEK_HKK', 1, 'ASTEK', 'RATE_JKK', 'Rate JKK', '2.5', 'N'),
('ASTEK_JK', 1, 'ASTEK', 'RATE_JK', 'Rate JK', '1.25', 'N'),
('ASTEK_JP', 1, 'ASTEK', 'RATE_JP', 'Rate JP', '3', 'N'),

('JKN_R', 1, 'JKN', 'RATE', 'Rate', null, 'N'),
('JKN_P', 1, 'JKN', 'PAY_VALUE', 'Pay Value', null, 'N'),

('JHT_R', 1, 'JHT', 'RATE', 'Rate', null, 'N'),
('JHT_P', 1, 'JHT', 'PAY_VALUE', 'Pay Value', null, 'N'),

('JKK_R', 1, 'JKK', 'RATE', 'Rate', null, 'N'),
('JKK_P', 1, 'JKK', 'PAY_VALUE', 'Pay Value', null, 'N'),

('JK_R', 1, 'JK', 'RATE', 'Rate', null, 'N'),
('JK_P', 1, 'JK', 'PAY_VALUE', 'Pay Value', null, 'N'),

('JP_R', 1, 'JP', 'RATE', 'Rate', null, 'N'),
('JP_P', 1, 'JP', 'PAY_VALUE', 'Pay Value', null, 'N'),

('TAX_P', 1, 'TAX', 'PAY_VALUE', 'Pay Value', null, 'N'),

('R_BASIC_SAL_P', null, 'R_BASIC_SAL', 'PAY_VALUE', 'Pay Value', null, 'N'),
('R_BASIC_SAL_D', null, 'R_BASIC_SAL', 'DESCRIPTION', 'Description', null, 'C'),

('R_FIXED_ALW_P', 1, 'R_FIXED_ALW', 'PAY_VALUE', 'Pay Value', null, 'N'),
('R_FIXED_ALW_D', 1, 'R_FIXED_ALW', 'DESCRIPTION', 'Description', null, 'C'),

('R_FUNC_ALW_P', 1, 'R_FUNC_ALW', 'PAY_VALUE', 'Pay Value', null, 'N'),
('R_FUNC_ALW_D', 1, 'R_FUNC_ALW', 'DESCRIPTION', 'Description', null, 'C'),

('R_FAMILY_ALW_P', 1, 'R_FAMILY_ALW', 'PAY_VALUE', 'Pay Value', null, 'N'),
('R_FAMILY_ALW_D', 1, 'R_FAMILY_ALW', 'DESCRIPTION', 'Description', null, 'C'),

('R_OT_1_5X_P', 1, 'R_OT_1_5X', 'PAY_VALUE', 'Pay Value', null, 'N'),
('R_OT_1_5X_D', 1, 'R_OT_1_5X', 'DESCRIPTION', 'Description', null, 'C'),

('R_OT_2X_P', 1, 'R_OT_2X', 'PAY_VALUE', 'Pay Value', null, 'N'),
('R_OT_2X_D', 1, 'R_OT_2X', 'DESCRIPTION', 'Description', null, 'C'),

('R_OT_3X_P', 1, 'R_OT_3X', 'PAY_VALUE', 'Pay Value', null, 'N'),
('R_OT_3X_D', 1, 'R_OT_3X', 'DESCRIPTION', 'Description', null, 'C'),

('R_OT_4X_P', 1, 'R_OT_4X', 'PAY_VALUE', 'Pay Value', null, 'N'),
('R_OT_4X_D', 1, 'R_OT_4X', 'DESCRIPTION', 'Description', null, 'C');

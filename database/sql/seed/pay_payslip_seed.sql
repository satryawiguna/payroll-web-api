/*
 * Copyright (c) 2021 All Rights Reserved.
 *
 * Unauthorized copying of this file, via any medium is strictly prohibited.
 * Proprietary and confidential.
 *
 * Written by:
 *   - Satrya Wiguna <satrya@freshcms.net>
 */

insert ignore into pay_payslip_group(group_id, company_id, group_name, group_type, hide_when_empty, seq_no)
values
('FIXED_SAL', 1, 'Gaji Tetap', '+', 0, 1),
('ALW', 1, 'Tunjangan', '+', 1, 2),
('SEV', 1, 'Apresiasi/Penghargaan', '+', 1, 3),
('DEDUCT', 1, 'Potongan', '-', 1, 4);


insert ignore into pay_payslip(payslip_id, company_id, group_id, element_id, balance_id, hide_when_empty, label, seq_no)
values
('BASIC_SAL', 1, 'FIXED_SAL', 'BASIC_SAL', null, 1, null, 1010),
('FIXED_ALW', 1, 'FIXED_SAL', 'FIXED_ALW', null, 1, null, 1020),
('FUNC_ALW', 1, 'ALW', 'FUNC_ALW', null, 1, null, 2010),
('ATTENDANCE', 1, 'ALW', 'ATTENDANCE', null, 1, null, 2020),
('FAMILY_ALW', 1, 'ALW', 'FAMILY_ALW', null, 1, null, 2030),
('OT', 1, 'ALW', 'OT', null, 1, null, 2040),
('OT_1_5X', 1, 'ALW', 'OT_1_5X', null, 1, null, 2041),
('OT_2X', 1, 'ALW', 'OT_2X', null, 1, null, 2042),
('OT_3X', 1, 'ALW', 'OT_3X', null, 1, null, 2043),
('OT_4X', 1, 'ALW', 'OT_4X', null, 1, null, 2044),
('HOLIDAY_ALW', 1, 'ALW', 'HOLIDAY_ALW', null, 1, null, 2050),
('SEV_ALW', 1, 'SEV', 'SEV_ALW', null, 1, null, 2060),
('LOAN', 1, 'DEDUCT', 'LOAN', null, 1, null, 2070),
('ASTEK', 1, 'DEDUCT', 'ASTEK', null, 1, null, 2080),
('JKN', 1, 'DEDUCT', 'JKN', null, 1, null, 2081),
('JHT', 1, 'DEDUCT', 'JHT', null, 1, null, 2082),
('JKK', 1, 'DEDUCT', 'JKK', null, 1, null, 2083),
('JK', 1, 'DEDUCT', 'JK', null, 1, null, 2084),
('JP', 1, 'DEDUCT', 'JP', null, 1, null, 2085),
('TAX', 1, 'DEDUCT', 'TAX', null, 0, null, 2090),

('R_BASIC_SAL', 1, 'FIXED_SAL', 'R_BASIC_SAL', null, 1, null, 3010),
('R_FIXED_ALW', 1, 'FIXED_SAL', 'R_FIXED_ALW', null, 1, null, 3020),
('R_FUNC_ALW', 1, 'ALW', 'R_FUNC_ALW', null, 1, null, 4010),
('R_FAMILY_ALW', 1, 'ALW', 'R_FAMILY_ALW', null, 1, null, 4030),
('R_OT_1_5X', 1, 'ALW', 'R_OT_1_5X', null, 1, null, 4041),
('R_OT_2X', 1, 'ALW', 'R_OT_2X', null, 1, null, 4042),
('R_OT_3X', 1, 'ALW', 'R_OT_3X', null, 1, null, 4043),
('R_OT_4X', 1, 'ALW', 'R_OT_4X', null, 1, null, 4044);

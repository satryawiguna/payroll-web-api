/*
 * Copyright (c) 2021 All Rights Reserved.
 *
 * Unauthorized copying of this file, via any medium is strictly prohibited.
 * Proprietary and confidential.
 *
 * Written by:
 *   - Satrya Wiguna <satrya@freshcms.net>
 */

insert ignore into pay_per_entry(entry_id, company_id, employee_id, element_id)
values
('BASIC_SAL_1', 1, 1, 'BASIC_SAL'),
('LOAN_1', 1, 1, 'LOAN');

insert ignore into pay_per_entry_value(value_id, company_id, entry_id, input_value_id, value)
values
('BASIC_SAL_A_1', 1, 'BASIC_SAL_1', 'BASIC_SAL_A', '5000000'),
('LOAN_D_1', 1, 'LOAN_1', 'LOAN_D', 'Koperasi Serba Ada'),
('LOAN_P_1', 1, 'LOAN_1', 'LOAN_P', '500000');

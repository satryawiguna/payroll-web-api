/*
 * Copyright (c) 2021 All Rights Reserved.
 *
 * Unauthorized copying of this file, via any medium is strictly prohibited.
 * Proprietary and confidential.
 *
 * Written by:
 *   - Satrya Wiguna <satrya@freshcms.net>
 */

insert ignore into pay_salary_basis(
    salary_basis_id, company_id, salary_basis_code, salary_basis_name, element_id, input_value_id, description
)
values
('BASIC_NET', null, 'BASIS_NET', 'Salary Basis Net', 'BASIC_SAL', 'BASIC_SAL_A', null);

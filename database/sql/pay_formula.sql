/*
 * Copyright (c) 2021 All Rights Reserved.
 *
 * Unauthorized copying of this file, via any medium is strictly prohibited.
 * Proprietary and confidential.
 *
 * Written by:
 *   - Satrya Wiguna <satrya@freshcms.net>
 */

/* sp_pay_basic */

delimiter $$
drop procedure if exists sp_pay_basic $$

create procedure sp_pay_basic(
    _process_id int, _per_process_id int, _effective date, _amount decimal(17, 2),
    out _pay_value decimal(17, 2)
)
begin
    set _pay_value = _amount;
end $$

delimiter ;


/* sp_pay_attendance */

delimiter $$
drop procedure if exists sp_pay_attendance $$

create procedure sp_pay_attendance(
    _process_id int, _per_process_id int, _effective date, _amount decimal(17, 2),
    out _day int, out _pay_value decimal(17, 2)
)
begin
    set _day = 19;
    set _pay_value = _amount * _day;
end $$

delimiter ;


/* sp_pay_family */

delimiter $$
drop procedure if exists sp_pay_family $$

create procedure sp_pay_family(
    _process_id int, _per_process_id int, _effective date, _amount decimal(17, 2),
    out _child_count int, out _pay_value decimal(17, 2)
)
begin
    set _child_count = 3;
    set _pay_value = _amount * _child_count;
end $$

delimiter ;


/* sp_pay_overtime */

delimiter $$
drop procedure if exists sp_pay_overtime $$

create procedure sp_pay_overtime(
    _process_id int, _per_process_id int, _effective date,
    out _hour_1_5x float, out _hour_2x float, out _hour_3x float, out _hour_4x float,
    out _overtime_1_5x decimal(17, 2), out _overtime_2x decimal(17, 2), out _overtime_3x decimal(17, 2), out _overtime_4x decimal(17, 2)
)
begin
    declare _amount decimal(17, 2);

    set _amount = 100000;

    set _hour_1_5x = 1.5;
    set _hour_2x = 2;
    set _hour_3x = 3;
    set _hour_4x = 4;

    set _overtime_1_5x = _hour_1_5x * _amount;
    set _overtime_2x = _hour_2x * _amount;
    set _overtime_3x = _hour_3x * _amount;
    set _overtime_4x = _hour_4x * _amount;
end $$

delimiter ;


/* sp_pay_holiday */

delimiter $$
drop procedure if exists sp_pay_holiday $$

create procedure sp_pay_holiday(
    _process_id int, _per_process_id int, _effective date,
    out _description varchar(100), out _pay_value decimal(17, 2)
)
begin
    set _description = 'Hari Raya Galungan';
    set _pay_value = 140000;
end $$

delimiter ;


/* sp_pay_severance */

delimiter $$
drop procedure if exists sp_pay_severance $$

create procedure sp_pay_severance(
    _process_id int, _per_process_id int, _effective date,
    out _description varchar(100), out _pay_value decimal(17, 2)
)
begin
    set _description = 'Pecat karena mengkritik saya';
    set _pay_value = 5000;
end $$

delimiter ;


/* sp_pay_astek */

delimiter $$
drop procedure if exists sp_pay_astek $$

create procedure sp_pay_astek(
    _process_id int, _per_process_id int, _effective date,
    _rate_jkn float, _rate_jht float, _rate_jkk float, _rate_jk float, _rate_jp float,
    out _value_jkn decimal(17, 2), out _value_jht decimal(17, 2), out _value_jkk decimal(17, 2), out _value_jk decimal(17, 2), out _value_jp decimal(17, 2)
)
begin
    declare _amount decimal(17, 2);

    set _amount = 50000;

    set _value_jkn = _rate_jkn * _amount;
    set _value_jht = _rate_jht * _amount;
    set _value_jkk = _rate_jkk * _amount;
    set _value_jk = _rate_jk * _amount;
    set _value_jp = _rate_jp * _amount;
end $$

delimiter ;


/* sp_pay_tax */

delimiter $$
drop procedure if exists sp_pay_tax $$

create procedure sp_pay_tax(
    _process_id int, _per_process_id int, _effective date,
    out _pay_value decimal(17, 2)
)
begin
    set _pay_value = 3000;
end $$

delimiter ;

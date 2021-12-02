/*
 * Copyright (c) 2021 All Rights Reserved.
 *
 * Unauthorized copying of this file, via any medium is strictly prohibited.
 * Proprietary and confidential.
 *
 * Written by:
 *   - Satrya Wiguna <satrya@freshcms.net>
 */

delimiter $$
drop function if exists to_decimal $$

create function to_decimal(_value varchar(500))
returns decimal(17,2)
begin
    if _value is null then return null; end if;
    return cast(_value as decimal(17,2));
end $$

delimiter ;

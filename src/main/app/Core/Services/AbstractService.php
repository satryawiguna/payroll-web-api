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

namespace App\Core\Services;

use App\Core\Auth\UserPrincipal;
use App\Core\Repositories\AbstractRepository;
use App\Core\Repositories\RepositoryTrait;

/**
 * @property AbstractRepository | RepositoryTrait $repo
 */
abstract class AbstractService extends BaseService
{
    function getPage(?array $criteria, UserPrincipal $user, ?array $options = null): array
    {
        $q = $this->repo->getAll($user->company_id, $criteria, $options);
        return $this->paginate($q, $criteria['per_page']);
    }

    function getOne($id, ?array $columns, UserPrincipal $user, ?array $options = null): ?object
    {
        $q = $this->repo->getOne($id, $user->company_id, $columns, $options);
        return $q->first();
    }

    function insert(array $data, UserPrincipal $user): object
    {
        $this->validateInsert($data, $user);

        $toInsert = $this->getDataForInsert($data, null, $user);
        $ret = $this->repo->insert($toInsert);

        $id = $toInsert[$this->getPrimaryKey()] ?? $ret;
        return (object) ['new_id' => $id];
    }

    protected function getDataForInsert(array $data, ?object $existing, UserPrincipal $user): array
    {
        if ($existing !== null) {
            foreach ($existing as $key => $value) {
                if (!array_key_exists($key, $data)) $data[$key] = $value;
            }
        }
        $data[$this->getPrimaryKey()] = generate_id();
        $data['company_id'] = $user->company_id;
        $data['created_by'] = $user->username;
        return $data;
    }

    function update($id, array $data, UserPrincipal $user): object
    {
        $existing = $this->repo->getExisting($id, $user->company_id);
        $this->validateUpdate($data, $existing, $user);

        $toUpdate = $this->getDataForUpdate($data, $existing, $user);
        $count = $this->repo->updateById($id, $toUpdate);
        return (object) ['count' => $count];
    }

    protected function getDataForUpdate(array $data, ?object $existing, UserPrincipal $user): array
    {
        if ($existing !== null) {
            foreach ($existing as $key => $value) {
                if (!array_key_exists($key, $data)) $data[$key] = $value;
            }
        }
        $data['updated_by'] = $user->username;
        return $data;
    }

    function delete($id, UserPrincipal $user): object
    {
        $existing = $this->repo->getExisting($id, $user->company_id);
        if ($existing === null) abort(404, "No data to delete");

        $this->validateDelete($existing, $user);
        $count = $this->repo->deleteById($id);
        return (object) ['count' => $count];
    }
}

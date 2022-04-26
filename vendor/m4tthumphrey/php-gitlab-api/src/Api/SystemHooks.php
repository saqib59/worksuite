<?php

declare(strict_types=1);

namespace Gitlab\Api;

class SystemHooks extends AbstractApi
{
    /**
     * @return mixed
     */
    public function all()
    {
        return $this->get('hooks');
    }

    /**
     * @param string $url
     *
     * @return mixed
     */
    public function create(string $url)
    {
        return $this->post('hooks', [
            'url' => $url,
        ]);
    }

    /**
     * @param int $id
     *
     * @return mixed
     */
    public function test(int $id)
    {
        return $this->get('hooks/'.self::encodePath($id));
    }

    /**
     * @param int $id
     *
     * @return mixed
     */
    public function remove(int $id)
    {
        return $this->delete('hooks/'.self::encodePath($id));
    }
}

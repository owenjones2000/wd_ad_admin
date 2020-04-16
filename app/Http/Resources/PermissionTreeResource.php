<?php

namespace App\Http\Resources;

use Illuminate\Http\Resources\Json\JsonResource;

class PermissionTreeResource extends JsonResource
{
    /**
     * Transform the resource into an array.
     *
     * @param  \Illuminate\Http\Request  $request
     * @return array
     */
    public function toArray($request)
    {
        return $this->buildPermissionTree($this);
    }

    private function buildPermissionTree($permission){
        $permission_resource = [
            'name' => $permission->name,
            'display_name' => $permission->display_name,
        ];
        foreach($permission->children as $child_permission){
            $permission_resource['children'][] = $this->buildPermissionTree($child_permission);
        }
        return $permission_resource;
    }
}

<?php

declare(strict_types=1);

namespace App\Model;

use Dleno\CommonCore\Model\BaseModel as CoreBaseModel;

/**
 * 项目模型基类。
 *
 * 统一继承 common-core 的 BaseModel，获得普通模型查询、分表、按主键路由分表等能力。
 *
 * 生成模型示例：
 * php bin/hyperf.php gen:model {table_name} --path app/Model/{Module}
 */
class BaseModel extends CoreBaseModel
{

}

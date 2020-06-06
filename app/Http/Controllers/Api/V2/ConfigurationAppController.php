<?php

namespace App\Http\Controllers\Api\V2;

use App\Models\ConfigurationApp;
use Illuminate\Http\Request;

/**
 * @resource v2 Configuration_app
 *
 * Api for Configuration_app
 */
class ConfigurationAppController extends Controller
{

    /**
     * GET v2/configuration_app
     *
     * Cho phép trả về danh sách các bản ghi cơ sở dữ liệu.
     *
     * Bạn cần lưu ý rằng hệ thộng mặc định chỉ trả về 30 bản ghi, nếu muốn lấy nhiều hơn bạn có thể truyền thêm các tham số.
     *
     * ### Thông tin cơ bản:
     * Info route | Description
     * --------- | -------
     * `version_route` | Version v2
     * `controller` | ConfigurationAppController
     * `route_name` | configuration_app
     *
     * ### Thông số lấy dữ liệu:
     * Trường dữ liệu (Param) | Default | Mô tả chi tiết
     * --------- | ------- | -------
     * `@fields` | NULL | Danh sách các trường dữ liệu configuration_app ví dụ (id,name,v..v.v..), nếu không truyền tham số này thì sẽ trả về 1 số trường mặc định.
     * `@orderby` | NULL | Trường ưu tiên sắp xếp trong configuration_app ví dụ (date), nếu không truyền tham số này thì sẽ sắp xếp mặc định.
     * `@limit` | NULL | Số lượng bản ghi configuration_app cần lấy ra, nếu không truyền tham số này thì sẽ mặc định là 30 bản ghi.
     * `@page` | NULL | Số thứ tự trang configuration_app cần lấy ra, nếu không truyền tham số này thì sẽ mặc định là trang 1.
     * `@where` | null | Bổ xung điều kiện lấy dữ liệu(http://doamin_api.com/api?where=id+1223,title+ads-asd-fasdf)
     * @return \Illuminate\Http\Response
     */
    function index(Request $request)
    {
        $this->validate($request, [
            'type' => 'string',
            'fields' => 'string|min:1|max:255',
            'where' => 'string|max:255|min:3',
        ]);
        $config_app = new ConfigurationApp();
        $config_app = $config_app->select($config_app->alias($this->fields));
        if ($this->where != null) $config_app = whereRawQueryBuilder($this->where, $config_app, 'mysql', 'configuration_app');
        $config_app = $config_app->orderBy('coa_id', 'desc')->get();
        return $this->setResponse(200, $config_app);
    }


    /**
     * GET v2/configuration_app
     *
     * Lấy dữ liệu theo id
     * ### Thông số lấy dữ liệu:
     * Trường dữ liệu (Param) | Mô tả chi tiết
     * --------- | -------
     * `@id` | ID configuration_app
     * @return \Illuminate\Http\Response
     */
    function show($id)
    {
        $config_app = new ConfigurationApp();
        $config_app = $config_app->select($config_app->alias($this->fields))->where('bkp_id', $id)->first();
        if ($config_app) {
            return $this->setResponse(200, $config_app);
        }
        return $this->setResponse(404, 'Not found data');
    }


    /**
     * POST v2/configuration_app
     *
     * Thêm dữ liệu vào database
     * ### Thông số lấy dữ liệu:
     * Trường dữ liệu (Param) | Mô tả chi tiết
     * --------- | -------
     * `@id` | ID configuration_app
     * `@fields` | List fields configuration_app
     * @return \Illuminate\Http\Response
     */
    function store(Request $request)
    {
        $config_app = new ConfigurationApp();
        $request = $request->all();
        unset($request['id']);
        unset($request['access_token']);
        $config_app = $this->setValueModel($request, $config_app);
        try {
            $config_app->save();
            return $this->setResponse(200, 'Saved');
        } catch (\Exception $error) {
            return $this->setResponse(500, $error->getMessage());
        }
    }

    /**
     * DELETE v2/configuration_app
     *
     * Xóa dữ liệu
     * ### Thông số lấy dữ liệu:
     * Trường dữ liệu (Param) | Mô tả chi tiết
     * --------- | -------
     * `@id` | ID configuration_app
     * `@fields` | List fields configuration_app
     * @return \Illuminate\Http\Response
     */
    function destroy($id)
    {
        $config_app = ConfigurationApp::find($id);
        if ($config_app) {
            if ($config_app->delete()) {
                return $this->setResponse(200, 'Deleted!');
            }
        }
        return $this->setResponse(500, null, 'Not found data');

    }

    /**
     * PUT v2/configuration_app/{id}
     *
     * Cập nhật dữ liệu theo `id`
     * ### Thông số lấy dữ liệu:
     * Trường dữ liệu (Param) | Mô tả chi tiết
     * --------- | -------
     * `@id` | ID configuration_app
     * `@fields` | List fields configuration_app
     * @return \Illuminate\Http\Response
     */
    function update(Request $request, $id)
    {
        $config_app = new ConfigurationApp();
        $request = $request->all();
        unset($request['id']);
        unset($request['access_token']);
        $config_app = $this->setValueModel($request, $config_app);
        try {
            $config_app->save();
            return $this->setResponse(200, 'Saved');
        } catch (\Exception $error) {
            return $this->setResponse(500, $error->getMessage());
        }
    }


    function setValueModel($request, $model)
    {
        foreach ($request as $key => $data) {
            if (in_array($key, $model->fillable)) {
                $model->{'coa_' . $key} = $data;
            }
        }
        return $model;
    }

}

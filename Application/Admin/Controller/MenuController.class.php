<?php
/**
 * Created by PhpStorm.
 * Author: Lin07ux
 * Date: 2017-01-25
 * Time: 23:32
 * Desc: 微信公众号自定义菜单
 */

namespace Admin\Controller;

use ZeroWeChat\Menu;
use ZeroWeChat\Wechat;

class MenuController extends CommonController
{
    /**
     * 获取自定义菜单列表
     */
    public function lists()
    {
        $menus = D('Menus')->menus();
        $res = ['code' => 0, 'msg' => '获取菜单列表成功', 'data' => $menus];

        $this->ajaxReturn($res);
    }

    /**
     * 保存菜单
     */
    public function save()
    {
        if (!IS_POST) $this->send404();

        $data = I('post.');
        if (isset($data['reply']) && isset($data['reply']['content'])) {
            $data['reply']['content'] = $_POST['reply']['content'];
        }

        $Menu = D('Menus');
        $result = $Menu->setMenu($data);
        $isEdit = isset($data['id']) && $data['id'] > 0 ? true : false;

        if ($result) {
            if ($isEdit) {
                $res = ['code' => 0, 'msg' => '更新菜单信息成功'];
            } else {
                $res = ['code' => 0, 'msg' => '添加菜单成功', 'data' => $result];
            }
        } else {
            $err = $Menu->getError();
            $msg = $isEdit ? '更新菜单信息失败' : '添加菜单失败';
            $res = ['code' => 1502, 'msg' => $err ?: $msg];
        }

        $this->ajaxReturn($res);
    }

    /**
     * 获取菜单回复信息
     */
    public function reply()
    {
        $Menu = D('Menus');
        $info = $Menu->getMenuReply(I('get.id'));

        if ($info) {
            $res = ['code' => 0, 'msg' => '获取子菜单信息成功', 'data' => $info];
        } else {
            $err = $Menu->getError();
            $res = ['code' => 1502, 'msg' => $err ?: '获取子菜单信息失败，请稍后重试。'];
        }

        $this->ajaxReturn($res);
    }

    /**
     * 删除菜单
     */
    public function remove()
    {
        if (!IS_POST) $this->send404();

        $Menu = D('Menus');
        $result = $Menu->remove(I('post.id'));

        if ($result) {
            $res = ['code' => 0, 'msg' => '菜单删除成功'];
        } else {
            $err = $Menu->getError();
            $res = ['code' => 1502, 'msg' => $err ?: '菜单删除失败！请稍后重试。'];
        }

        $this->ajaxReturn($res);
    }

    /**
     * 发布微信菜单
     */
    public function publish()
    {
        if (!IS_POST) $this->send404();

        $wechat = Wechat::getInstance(C('WECHAT.APPID'), C('WECHAT.SECRET'));
        $access_token = $wechat->getAccessToken();

        if ($access_token) {
            $menus = D('Menus')->menus(true);
            $Menu = new Menu($access_token);
            $result = $Menu->publish($menus);

            if ($result) {
                $res = ['code' => 0, 'msg' => '菜单发布成功'];
            } else {
                $err = $Menu->getError();
                $res = ['code' => 101, 'msg' => $err ?: '菜单发布失败！请稍后重试。'];
            }
        } else {
            $res = ['code' => 101, 'msg' => '获取微信公众号access_token失败。请稍后重试！'];
        }

        $this->ajaxReturn($res);
    }
}
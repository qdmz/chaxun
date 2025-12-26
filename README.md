# Excel数据查询系统

一个基于PHP的Excel数据查询系统，支持Excel文件上传、数据查询、导出和打印功能。

## 功能特性

- Excel数据文件上传和解析
- 快速数据查询功能
- 用户管理（注册、登录、权限控制）
- 数据导出为CSV格式
- 数据打印功能
- 响应式Web界面

## 安装要求

- PHP 7.4+
- MySQL 5.7+
- Web服务器（Apache/Nginx）

## 安装步骤

1. 将文件上传到Web服务器目录
2. 创建MySQL数据库
3. 修改 `includes/config.php` 文件配置数据库连接信息
4. 导入 `sql.sql` 文件创建数据表结构
5. 设置 `uploads/` 目录写入权限
6. 访问系统进行使用

## 使用说明

1. 管理员账户登录后台（`/admin`）
2. 上传Excel文件到系统
3. 用户可使用前台查询功能进行数据搜索

## 目录结构

```
├── admin/           # 管理后台
├── includes/        # 系统包含文件
├── user/           # 用户功能
├── uploads/        # 上传文件存储
├── assets/         # 静态资源
├── index.php       # 前台查询页面
└── sql.sql         # 数据库结构文件
```

## 许可证

MIT License

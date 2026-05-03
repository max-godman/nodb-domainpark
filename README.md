# NoDB-DomainPark

A lightweight domain parking system with zero database dependency - file-based storage

---

## Requirements

- **PHP 5.4.0+** (minimum version)
- **Optional**: To retrieve visitor country codes, your DNS/CDN provider must pass the `HTTP_CF_IPCOUNTRY` HTTP header (Cloudflare supports this feature)
- **No database required** - all data is stored in flat files
- **No additional PHP extensions needed** - uses only built-in PHP functions

## Installation Steps

1. Upload all files from the `php` directory to your website root directory
2. Visit `http://yourdomain.com/setup.php` to start installation
3. Enter admin username and binding domain (e.g., `admin` and `domain.com`)
4. System will auto-generate password (format: `username + 6-digit date`, e.g., `admin260501`)
5. After installation, you'll be redirected to the login page automatically
6. **Reinstallation**: Delete `inc/sys_admin.php` and visit `setup.php` to reinstall. Old data (domain configuration and visit records) will be automatically cleared.

## Admin Panel

1. Login URL: `http://yourdomain.com/adm/login.php`
2. Initial password format: `username + 6-digit installation date` (e.g., `admin260501`)
3. After login, you can manage domain parking settings:
   - Add/edit/delete domains
   - Set domain status (Disabled, Pending, 301 Redirect, 302 Redirect, Display Page)
   - View click statistics and visitor records
4. **Password change**: You can set any custom password in the "Settings" page, or leave it empty to keep the current password unchanged

## Features

1. **Domain Matching**: Supports any subdomain access, automatically extracts root domain for matching
2. **Data Storage**: All data stored in file system, no database required
3. **Visitor Tracking**: Records click count and recent N visitor records for each domain
4. **Friendship Links**: Manage footer links for all parked domains from admin panel
5. **Admin Domain Compatibility**: The domain bound for admin login can also be added to the domain parking list for redirect or display. Both functions work independently without conflict.

## Directory Structure

```
├── index.php          - Frontend homepage (domain parking handler)
├── setup.php          - Installation script
├── inc/               - Core configuration and function files
│   ├── inc-sha.php    - SHA256 encryption function
│   ├── sys_admin.php  - Admin configuration (auto-generated)
│   ├── domain.php     - Domain configuration (auto-generated)
│   └── link.php       - Friendship links (auto-generated)
├── adm/               - Admin management pages
│   ├── login.php      - Admin login
│   ├── dm.php         - Domain management
│   └── check.php      - Settings & password change
├── data/              - Domain data storage directory (JSON files)
└── pic/               - Static resources (images, CSS, JS)
```

## Notes

1. Ensure `inc/`, `adm/`, `data/`, and `pic/` directories have write permissions
2. Admin password is date-based on installation day; use the correct date format for initial login
3. Only domains with "Disabled" status can be deleted
4. This program can be freely copied and distributed. It is recommended to keep the original author's attribution comments.

---

## 中文说明

### 安装环境

- **最低PHP版本要求**：PHP 5.4.0+
- **非必要**：若需要获取客户端IP所属国家代码，需在解析端传递国家代码HTTP头 `HTTP_CF_IPCOUNTRY`（Cloudflare支持此项）
- **无需数据库** - 所有数据使用文件存储
- **无需额外PHP扩展** - 仅使用PHP内置函数

### 安装步骤

1. 将 `php` 目录下的所有文件上传到您的网站根目录
2. 访问 `http://yourdomain.com/setup.php` 开始安装
3. 填写管理员用户名和绑定域名（例如：`admin` 和 `domain.com`）
4. 系统会自动生成密码（格式：`用户名 + 6位年月日`，如 `admin260501`）
5. 安装完成后自动跳转到登录页面
6. **重新安装**：删除 `inc/sys_admin.php` 后访问 `setup.php` 即可重新安装，旧数据（域名配置和访问记录）会被自动清空

### 后台管理

1. 登录地址：`http://yourdomain.com/adm/login.php`
2. 初始密码格式：`用户名 + 安装时日期的6位年月日`（如 `admin260501`）
3. 登录后可管理域名停放设置：
   - 添加/编辑/删除域名
   - 设置域名状态（禁用、审核中、301跳转、302跳转、展示页面）
   - 查看点击统计和访问记录
4. **密码修改**：可在 "Settings" 页面修改为任意密码，留空则保持当前密码不变

### 功能说明

1. **域名匹配**：支持任意子域名访问，自动提取根域名进行匹配
2. **数据存储**：所有数据存储在文件系统中，无需数据库
3. **访问统计**：记录每个域名的点击次数和最近N条访问记录
4. **友情链接**：可在后台统一管理所有页面底部的友情链接
5. **后台域名兼容**：绑定后台登录的域名，仍然可以添加入域名停放列表，执行域名跳转或展示，两者不冲突

### 文件结构

```
├── index.php          - 前端首页（域名停放处理）
├── setup.php          - 安装脚本
├── inc/               - 核心配置和函数文件
│   ├── inc-sha.php    - SHA256加密函数
│   ├── sys_admin.php  - 管理员配置（自动生成）
│   ├── domain.php     - 域名配置（自动生成）
│   └── link.php       - 友情链接（自动生成）
├── adm/               - 后台管理页面
│   ├── login.php      - 管理员登录
│   ├── dm.php         - 域名管理
│   └── check.php      - 设置与密码修改
├── data/              - 域名数据存储目录（JSON文件）
└── pic/               - 静态资源目录（图片、CSS、JS）
```

### 注意事项

1. 确保 `inc/`、`adm/`、`data/`、`pic/` 目录有写入权限
2. 管理员初始密码基于安装当天日期生成，请使用正确的日期格式登录
3. 删除域名时只有状态为"禁用"的域名才能被删除
4. 本程序可以任意复制传播，建议保留原作者署名注释

---



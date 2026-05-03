# Changelog

All notable changes to NoDB-DomainPark will be documented in this file.

## [1.0.1] - 2026-05-03

### Security Enhancement
- **Data File Security**: Changed domain data files from plain text format (`data/domain`) to `.log` extension (`data/domain.log`) to prevent direct web access and downloading
- **Domain Management**: Made domain field read-only in edit form to prevent accidental changes (domains can only be deleted, not modified)
- **Setup Page**: Updated password example to show current date dynamically (e.g., admin + YYMMDD)

## [1.0.0] - 2026-05-02

### Initial Release
- File-based domain parking system with zero database dependency
- SHA256 password encryption
- Admin panel with login, domain management, settings, and friendship links
- Visitor tracking with click count and user agent records
- Support for domain redirect (301/302) and display modes
- Root domain extraction supporting various subdomain formats
- Bilingual documentation (English/Chinese)

### Added
- **setup.php**: Installation wizard with auto-generated password (username + YYMMDD)
- **index.php**: Frontend domain parking handler with type-based response logic
- **adm/login.php**: Admin login with session management and brute-force protection (5 attempts/day limit)
- **adm/dm.php**: Domain management (add/edit/delete), click statistics, visit records modal
- **adm/check.php**: Password/domain settings and friendship links management
- **inc/inc-sha.php**: SHA256 encryption utility

### Changed
- **setup.php**: Admin Domain field auto-fills with current visiting domain
- **setup.php**: Reinstallation now cleans old `inc/domain.php` and all files in `data/` directory
- **adm/login.php**: Password verification changed to direct SHA256 hash comparison (fixed login failure after password change)
- **adm/dm.php**: Domain status type preserves original value when editing (no longer resets to 1)
- **adm/dm.php**: Added top navigation menu (Domain Management / Settings / Friendship Links / Logout)
- **adm/dm.php**: Fixed visit records modal - implemented AJAX handler to load and display useragent data in table format
- **adm/dm.php**: Fixed edit form data loading - about, url, userdata fields now correctly populate via HTML data attributes
- **adm/dm.php**: Added Refresh button next to "Existing Domains" heading
- **adm/dm.php**: Domain names rendered as clickable links opening in new tab
- **adm/dm.php**: Pending status highlighting - yellow border + light background on Status dropdown with orange prompt text when editing a Pending domain; auto-removes when user changes status
- **adm/check.php**: Added "New Password" input field; leave empty to keep current password unchanged
- **adm/check.php**: Old password verification unified to SHA256 hash comparison only (removed plaintext fallback)
- **adm/check.php**: Added single link entry form (Link Text + Link URL) that auto-formats as `<li><a href="..." target="_blank">...</a></li>` and appends to existing links
- **adm/check.php**: Separated Settings and Friendship Links into different views via URL parameters (`?set=on` / `?links=on`); navigation links updated accordingly
- **index.php**: Same-IP deduplication - if current IP matches the last useragent record's IP, click count does not increment and no duplicate record is added
- **index.php**: Added `getRealIp()` function to retrieve real visitor IP behind Cloudflare/CDN proxies (prioritizes `HTTP_CF_CONNECTING_IP`, `HTTP_X_FORWARDED_FOR`, `HTTP_X_REAL_IP`)
- **index.php**: Fixed line breaks in About field - now properly renders newline characters as `<br>` tags in frontend display
- **README.md**: Merged chinese.txt and english.txt into bilingual README with Requirements section (PHP 5.4.0+, Cloudflare country code support, no database/extensions needed)
- **All PHP files**: Added author attribution comment header

### Documentation
- Installation step #6: Reinstallation instructions
- Feature #5: Admin-bound domain can also be added to parking list without conflict
- Note #4: Recommend keeping original author attribution comments
- Created bilingual README.md with directory structure tree
- Created LICENSE (MIT)
- Created CHANGELOG.md

### Technical Details
- **Minimum PHP Version**: 5.4.0+
- **Required Extensions**: None (uses only built-in PHP functions)
- **Storage**: JSON flat files with `.log` extension in `data/` directory
- **Encryption**: SHA256 via PHP `hash()` function

## Author
- **max-godman** - max_godman@foxmail.com
- GitHub: https://github.com/max-godman

---

# 更新日志

NoDB-DomainPark 的所有重要变更都将记录在此文件中。

## [1.0.1] - 2026-05-03

### 安全性增强
- **数据文件安全**：将域名数据文件从纯文本格式（`data/domain`）更改为 `.log` 扩展名（`data/domain.log`），防止直接网页访问和下载
- **域名管理**：在编辑表单中将域名字段设为只读，防止意外修改（域名只能删除，不能修改）
- **安装页面**：更新密码示例以动态显示当前日期（例如：admin + YYMMDD）

## [1.0.0] - 2026-05-02

### 初始版本发布
- 基于文件的域名停放系统，无需数据库依赖
- SHA256密码加密
- 管理员面板，包含登录、域名管理、设置和友情链接功能
- 访客跟踪，记录点击次数和用户代理记录
- 支持域名重定向（301/302）和展示模式
- 支持各种子域名格式的根域名提取
- 双语文档（英文/中文）

### 新增功能
- **setup.php**：安装向导，自动生成密码（用户名 + YYMMDD）
- **index.php**：前端域名停放处理器，基于类型响应逻辑
- **adm/login.php**：管理员登录，带会话管理和暴力破解保护（每天5次尝试限制）
- **adm/dm.php**：域名管理（添加/编辑/删除），点击统计，访问记录模态框
- **adm/check.php**：密码/域名设置和友情链接管理
- **inc/inc-sha.php**：SHA256加密工具

### 更改内容
- **setup.php**：管理员域名字段自动填充当前访问域名
- **setup.php**：重新安装时清理旧的 `inc/domain.php` 和 `data/` 目录中的所有文件
- **adm/login.php**：密码验证改为直接SHA256哈希比较（修复修改密码后登录失败的问题）
- **adm/dm.php**：编辑时域名状态类型保留原始值（不再重置为1）
- **adm/dm.php**：添加顶部导航菜单（域名管理 / 设置 / 友情链接 / 退出登录）
- **adm/dm.php**：修复访问记录模态框 - 实现AJAX处理器以表格格式加载和显示用户代理数据
- **adm/dm.php**：修复编辑表单数据加载 - about、url、userdata字段现在通过HTML数据属性正确填充
- **adm/dm.php**：在"现有域名"标题旁添加刷新按钮
- **adm/dm.php**：域名名称渲染为可点击链接，在新标签页中打开
- **adm/dm.php**：待审核状态高亮 - 编辑待审核域名时，状态下拉框显示黄色边框+浅色背景，并显示橙色提示文字；用户更改状态时自动移除
- **adm/check.php**：添加"新密码"输入字段；留空以保持当前密码不变
- **adm/check.php**：旧密码验证统一为SHA256哈希比较（移除明文回退）
- **adm/check.php**：添加单个链接录入表单（链接文字 + 链接URL），自动格式化为 `<li><a href="..." target="_blank">...</a></li>` 并追加到现有链接
- **adm/check.php**：通过URL参数（`?set=on` / `?links=on`）将设置和友情链接分离到不同视图；导航链接相应更新
- **index.php**：相同IP去重 - 如果当前IP与上一条用户代理记录的IP匹配，则不增加点击计数且不添加重复记录
- **index.php**：添加 `getRealIp()` 函数以获取Cloudflare/CDN代理后的真实访客IP（优先使用 `HTTP_CF_CONNECTING_IP`、`HTTP_X_FORWARDED_FOR`、`HTTP_X_REAL_IP`）
- **index.php**：修复About字段换行符 - 现在正确将换行符渲染为前端显示的 `<br>` 标签
- **README.md**：合并chinese.txt和english.txt为双语README，包含要求部分（PHP 5.4.0+、Cloudflare国家代码支持、无需数据库/扩展）
- **所有PHP文件**：添加作者署名注释头

### 文档说明
- 安装步骤第6条：重新安装说明
- 功能第5条：管理员绑定域名也可以添加到停放列表中，无冲突
- 注意事项第4条：建议保留原作者署名注释
- 创建了带目录结构树的双语README.md
- 创建了LICENSE（MIT）
- 创建了CHANGELOG.md

### 技术细节
- **最低PHP版本**：5.4.0+
- **所需扩展**：无（仅使用内置PHP函数）
- **存储**：`data/` 目录中的带 `.log` 扩展名的JSON平面文件
- **加密**：通过PHP `hash()` 函数的SHA256

---

## 作者
- **max-godman** - max_godman@foxmail.com
- GitHub: https://github.com/max-godman

// 主JavaScript文件
document.addEventListener('DOMContentLoaded', function() {
    // 表单验证
    const forms = document.querySelectorAll('form');
    forms.forEach(form => {
        form.addEventListener('submit', function(e) {
            const requiredFields = this.querySelectorAll('[required]');
            let isValid = true;
            
            requiredFields.forEach(field => {
                if (!field.value.trim()) {
                    field.style.borderColor = '#dc3545';
                    isValid = false;
                    
                    // 添加错误提示
                    let errorMsg = field.nextElementSibling;
                    if (!errorMsg || !errorMsg.classList.contains('error-msg')) {
                        errorMsg = document.createElement('div');
                        errorMsg.className = 'error-msg';
                        errorMsg.style.color = '#dc3545';
                        errorMsg.style.fontSize = '0.875rem';
                        errorMsg.style.marginTop = '0.25rem';
                        errorMsg.textContent = '此字段为必填项';
                        field.parentNode.appendChild(errorMsg);
                    }
                } else {
                    field.style.borderColor = '#e9ecef';
                    
                    // 移除错误提示
                    const errorMsg = field.nextElementSibling;
                    if (errorMsg && errorMsg.classList.contains('error-msg')) {
                        errorMsg.remove();
                    }
                }
            });
            
            if (!isValid) {
                e.preventDefault();
                showAlert('请填写所有必填字段', 'error');
            }
        });
    });
    
    // 文件上传预览
    const fileInputs = document.querySelectorAll('input[type="file"]');
    fileInputs.forEach(input => {
        input.addEventListener('change', function(e) {
            const file = this.files[0];
            if (file) {
                // 检查文件大小
                const maxSize = 10 * 1024 * 1024; // 10MB
                if (file.size > maxSize) {
                    showAlert('文件大小不能超过10MB', 'error');
                    this.value = '';
                    return;
                }
                
                // 检查文件类型
                const allowedExtensions = ['xls', 'xlsx', 'csv'];
                const fileExtension = file.name.split('.').pop().toLowerCase();
                
                if (!allowedExtensions.includes(fileExtension)) {
                    showAlert('只支持 .xls, .xlsx, .csv 格式的文件', 'error');
                    this.value = '';
                    return;
                }
                
                showAlert(`已选择文件: ${file.name} (${formatFileSize(file.size)})`, 'success');
            }
        });
    });
    
    // 搜索框自动焦点
    const searchInput = document.getElementById('keyword');
    if (searchInput) {
        searchInput.focus();
    }
    
    // 表格排序功能
    const tables = document.querySelectorAll('.data-table, .admin-table');
    tables.forEach(table => {
        const headers = table.querySelectorAll('th');
        headers.forEach((header, index) => {
            header.style.cursor = 'pointer';
            header.addEventListener('click', function() {
                sortTable(table, index);
            });
        });
    });
    
    // 响应式导航菜单
    const navToggle = document.createElement('button');
    navToggle.innerHTML = '<i class="fas fa-bars"></i>';
    navToggle.className = 'nav-toggle';
    navToggle.style.cssText = `
        display: none;
        background: none;
        border: none;
        color: white;
        font-size: 1.5rem;
        cursor: pointer;
        position: absolute;
        right: 20px;
        top: 20px;
    `;
    
    const header = document.querySelector('header');
    if (header) {
        header.style.position = 'relative';
        const nav = header.querySelector('nav');
        if (nav) {
            header.insertBefore(navToggle, nav);
            
            navToggle.addEventListener('click', function() {
                nav.style.display = nav.style.display === 'flex' ? 'none' : 'flex';
            });
            
            // 媒体查询
            const mediaQuery = window.matchMedia('(max-width: 768px)');
            function handleMobileChange(e) {
                if (e.matches) {
                    navToggle.style.display = 'block';
                    nav.style.display = 'none';
                } else {
                    navToggle.style.display = 'none';
                    nav.style.display = 'flex';
                }
            }
            
            mediaQuery.addListener(handleMobileChange);
            handleMobileChange(mediaQuery);
        }
    }
});

// 表格排序函数
function sortTable(table, columnIndex) {
    const tbody = table.querySelector('tbody');
    const rows = Array.from(tbody.querySelectorAll('tr'));
    const isAscending = table.getAttribute('data-sort-dir') !== 'asc';
    
    rows.sort((a, b) => {
        const aText = a.children[columnIndex].textContent.trim();
        const bText = b.children[columnIndex].textContent.trim();
        
        // 尝试转换为数字比较
        const aNum = parseFloat(aText.replace(/[^0-9.-]+/g, ''));
        const bNum = parseFloat(bText.replace(/[^0-9.-]+/g, ''));
        
        if (!isNaN(aNum) && !isNaN(bNum)) {
            return isAscending ? aNum - bNum : bNum - aNum;
        }
        
        // 字符串比较
        return isAscending 
            ? aText.localeCompare(bText)
            : bText.localeCompare(aText);
    });
    
    // 移除旧行
    while (tbody.firstChild) {
        tbody.removeChild(tbody.firstChild);
    }
    
    // 添加排序后的行
    rows.forEach(row => tbody.appendChild(row));
    
    // 更新排序方向
    table.setAttribute('data-sort-dir', isAscending ? 'asc' : 'desc');
    
    // 更新表头指示器
    const headers = table.querySelectorAll('th');
    headers.forEach(header => header.classList.remove('sorted-asc', 'sorted-desc'));
    headers[columnIndex].classList.add(isAscending ? 'sorted-asc' : 'sorted-desc');
}

// 显示提示消息
function showAlert(message, type = 'info') {
    const alert = document.createElement('div');
    alert.className = `alert alert-${type}`;
    alert.innerHTML = `
        <i class="fas fa-${getIconByType(type)}"></i>
        ${message}
    `;
    alert.style.cssText = `
        position: fixed;
        top: 20px;
        right: 20px;
        z-index: 1000;
        animation: slideIn 0.3s ease-out;
    `;
    
    document.body.appendChild(alert);
    
    // 3秒后自动移除
    setTimeout(() => {
        alert.style.animation = 'slideOut 0.3s ease-out';
        setTimeout(() => alert.remove(), 300);
    }, 3000);
    
    // 添加动画关键帧
    if (!document.getElementById('alert-animations')) {
        const style = document.createElement('style');
        style.id = 'alert-animations';
        style.textContent = `
            @keyframes slideIn {
                from { transform: translateX(100%); opacity: 0; }
                to { transform: translateX(0); opacity: 1; }
            }
            @keyframes slideOut {
                from { transform: translateX(0); opacity: 1; }
                to { transform: translateX(100%); opacity: 0; }
            }
        `;
        document.head.appendChild(style);
    }
}

// 根据消息类型获取图标
function getIconByType(type) {
    const icons = {
        'success': 'check-circle',
        'error': 'exclamation-circle',
        'warning': 'exclamation-triangle',
        'info': 'info-circle'
    };
    return icons[type] || 'info-circle';
}

// 格式化文件大小
function formatFileSize(bytes) {
    if (bytes === 0) return '0 Bytes';
    const k = 1024;
    const sizes = ['Bytes', 'KB', 'MB', 'GB'];
    const i = Math.floor(Math.log(bytes) / Math.log(k));
    return parseFloat((bytes / Math.pow(k, i)).toFixed(2)) + ' ' + sizes[i];
}
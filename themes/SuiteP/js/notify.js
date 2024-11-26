class AlertObj {
    constructor() {
        this.title = 'Alert';
        this.options = {
            body: ' ',
            url_redirect: '',
            target_module: '',
            type: 'info'
        };
    }
}

class Alerts {
    constructor() {
        this.replaceMessages = [];
    }

    /**
      * Request permission to use Notification API
      */
    async requestPermission() {
        if (!("Notification" in window)) {
            return false;
        }
        const permission = await Notification.requestPermission();
        return permission === "granted";
    }

    /**
     * Enable Notifications API
     */
    enable() {
        const alert = new AlertObj();

        if (!("Notification" in window)) {
            alert.title = SUGAR.language.translate('app_strings', 'MSG_BROWSER_NOTIFICATIONS_UNSUPPORTED');
            this.show(alert);
            return;
        }

        Notification.requestPermission((permission) => {
            alert.title = SUGAR.language.translate(
                'app_strings',
                permission === "granted"
                    ? 'MSG_BROWSER_NOTIFICATIONS_ENABLED'
                    : 'MSG_BROWSER_NOTIFICATIONS_DISABLED'
            );
            this.show(alert);
        });
    }

    /**
     * Show an alert to the user
     * @param {AlertObj} alertObj
     */
    show(alertObj) {
        this.requestPermission();

        if (!("Notification" in window)) return;

        if (Notification.permission === "granted") {
            const options = alertObj.options || {};

            if (options.target_module) {
                options.icon = `index.php?entryPoint=getImage&themeName=${SUGAR.themes.theme_name || ''}&imageName=${options.target_module}s.gif`;
            }

            options.type = options.type || 'info';

            // Hiển thị thông báo
            const notification = new Notification(alertObj.title, options);

            if (options.url_redirect) {
                notification.onclick = () => window.open(options.url_redirect);
            }
        } else {
            // Xử lý khi không có quyền gửi thông báo
            let message = alertObj.title;

            if (alertObj.options?.body) {
                message += `\n${alertObj.options.body}`;
            }

            message += `\n${SUGAR.language.translate('app_strings', 'MSG_JS_ALERT_MTG_REMINDER_CALL_MSG')}\n\n`;

            if (confirm(message) && alertObj.options?.url_redirect) {
                window.location = alertObj.options.url_redirect;
            }
        }
    }


    /**
     * Redirect to login page
     * @return {boolean}
     */
    redirectToLogin() {
        const params = new URLSearchParams(window.location.search);
        if (params.get('entryPoint') !== 'Changenewpassword' &&
            params.get('module') !== 'Users' &&
            params.get('action') !== 'Login') {
            window.location.href = 'index.php?module=Users&action=Login&loginErrorMessage=LBL_SESSION_EXPIRED';

            return true;
        }

        return false;
    }

    /**
     * Add alert to manager instead of showing it to the user
     * @param {AlertObj} AlertObj
     */
    async addToManager(alertObj) {
        const postData = {
            module: 'Alerts',
            action: 'add',
            name: alertObj.title,
            description: alertObj.options.body || '',
            url_redirect: alertObj.options.url_redirect || '',
            is_read: 0,
            target_module: alertObj.options.target_module || '',
            type: alertObj.options.type || 'info',
            reminder_id: alertObj.options.reminder_id || ''
        };

        try {
            const response = await $.post('index.php', postData);
            const data = JSON.parse(response);
            if (data?.result === 1) {
                this.show(alertObj);
            }
        } catch (error) {
            console.error('Failed to add alert:', error);
        } finally {
            this.updateManager();
        }
    }

    /**
     * Update Alert Manager (Navigation bar element) - ALL Alerts
     */
    async updateManager() {
        try {
            let data = await $.ajax('index.php?module=Alerts&action=get&to_pdf=1');

            if (data === 'lost session') {
                this.redirectToLogin();
                return;
            }

            // Thay thế chuỗi thông báo
            if (this.replaceMessages && typeof this.replaceMessages === 'object') {
                for (const replaceMessage in this.replaceMessages) {
                    if (this.replaceMessages.hasOwnProperty(replaceMessage)) {
                        data = data.replace(
                            this.replaceMessages[replaceMessage].search,
                            this.replaceMessages[replaceMessage].replace
                        );
                    }
                }
            }

            // Cập nhật giao diện thông báo
            const alertsDiv = $('.desktop_notifications #alerts');
            alertsDiv.html(data);

            // Tính toán số lượng thông báo
            const alertsContainer = $('<div></div>').html(data);
            const alertCount = alertsContainer.find('.alert').length;
            if (alertCount > 99) {
                alertCount = '99+';
            }
            $('.alert_count').text(alertCount);
            $('.alert_count').toggleClass('bg-danger', alertCount > 0);
            $('.desktop_notifications').toggleClass('has-alerts', alertCount > 0);
        } catch (error) {
            console.error('Failed to update alerts:', error);
        }
    }

    /**
     * Update Alert Manager (Navigation bar element) - Unread Alerts
     */
    static async getAlertUnread() {
        try {
            let data = await $.ajax('index.php?module=Alerts&action=get_unread&to_pdf=1');

            if (data === 'lost session') {
                this.redirectToLogin();
                return;
            }

            // Thay thế chuỗi thông báo
            if (this.replaceMessages && typeof this.replaceMessages === 'object') {
                for (const replaceMessage in this.replaceMessages) {
                    if (this.replaceMessages.hasOwnProperty(replaceMessage)) {
                        data = data.replace(
                            this.replaceMessages[replaceMessage].search,
                            this.replaceMessages[replaceMessage].replace
                        );
                    }
                }
            }

            // Cập nhật giao diện thông báo
            const alertsDiv = $('.desktop_notifications #alerts');
            alertsDiv.html(data);

            // Tính toán số lượng thông báo
            const alertsContainer = $('<div></div>').html(data);
            const alertCount = alertsContainer.find('.alert').length;
            if (alertCount > 99) {
                alertCount = '99+';
            }
            $('.alert_count').text(alertCount);
            $('.alert_count').toggleClass('bg-danger', alertCount > 0);
            $('.desktop_notifications').toggleClass('has-alerts', alertCount > 0);
        } catch (error) {
            console.error('Failed to update alerts:', error);
        }
    }

    /**
     * Mark alert as read
     * @param {string} id
     */
    static async markAsRead(id) {
        try {
            await $.ajax(`index.php?module=Alerts&action=markAsRead&record=${id}&to_pdf=1`);
            this.getAlertUnread();
        } catch (error) {
            console.error('Failed to mark alert as read:', error);
        }
    }

    /**
     * Mark alert as read
     * @param {string} id
     */
    static async clearAlert(id) {
        const alerts = new Alerts();

        try {
            await $.ajax(`index.php?module=Alerts&action=clearAlert&record=${id}&to_pdf=1`);
            alerts.updateManager();
        } catch (error) {
            console.error('Failed to mark alert as read:', error);
        }
    }

    /**
     * Runs timer to update alerts
     */
    static getAllAlerts() {
        const alerts = new Alerts();

        alerts.replaceMessages = [
            { search: SUGAR.language.translate("app", "MSG_JS_ALERT_MTG_REMINDER_CALL_MSG"), replace: "" },
            { search: SUGAR.language.translate("app", "MSG_JS_ALERT_MTG_REMINDER_MEETING_MSG"), replace: "" }
        ];

        const update_alerts = async () => {
            await alerts.updateManager();
        };
        setTimeout(update_alerts, 10);
    }
}


$(document).ready(() => {
    Alerts.getAlertUnread();
});
// Sistem notifikasi real-time
class NotificationSystem {
  constructor() {
    this.lastNotificationId = 0;
    this.container = null;
    this.checkInterval = 30000; // Check every 30 seconds
    this.cronInterval = 5 * 60 * 1000; // Check rental deadlines every 5 minutes
    this.initialize();
  }

  initialize() {
    // Create notification container if it doesn't exist
    if (!document.getElementById("notification-container")) {
      this.container = document.createElement("div");
      this.container.id = "notification-container";
      document.body.appendChild(this.container);
    } else {
      this.container = document.getElementById("notification-container");
    }

    // Start checking for new notifications
    this.checkNotifications();
    setInterval(() => this.checkNotifications(), this.checkInterval);

    // Start checking for rental deadlines (similar to cronjob)
    this.checkRentalDeadlines();
    setInterval(() => this.checkRentalDeadlines(), this.cronInterval);
  }

  checkNotifications() {
    // Fetch new notifications using AJAX
    const baseUrl = this.getBaseUrl();
    fetch(baseUrl + "get_notifications.php?last_id=" + this.lastNotificationId)
      .then((response) => response.json())
      .then((data) => {
        if (data.success && data.notifications.length > 0) {
          // Update last notification ID
          this.lastNotificationId =
            data.notifications[data.notifications.length - 1].notification_id;

          // Display new notifications
          data.notifications.forEach((notification) => {
            this.showNotification(notification);
          });

          // Update notification count in navbar
          this.updateNotificationCount(data.unread_count);
        }
      })
      .catch((error) => console.error("Error fetching notifications:", error));
  }

  // Fungsi ini akan memeriksa penyewaan yang mendekati tenggat waktu
  checkRentalDeadlines() {
    const baseUrl = this.getBaseUrl();
    fetch(baseUrl + "check_deadlines.php")
      .then((response) => response.json())
      .then((data) => {
        if (
          data.success &&
          data.notifications &&
          data.notifications.length > 0
        ) {
          // Display new deadline notifications
          data.notifications.forEach((notification) => {
            this.showNotification(notification);
          });

          // Update last notification ID if needed
          if (data.notifications.length > 0) {
            const maxId = Math.max(
              ...data.notifications.map((n) => n.notification_id)
            );
            if (maxId > this.lastNotificationId) {
              this.lastNotificationId = maxId;
            }
          }

          // Update notification count
          if (data.unread_count !== undefined) {
            this.updateNotificationCount(data.unread_count);
          }
        }
      })
      .catch((error) =>
        console.error("Error checking rental deadlines:", error)
      );
  }

  showNotification(notification) {
    // Create notification element
    const notifElement = document.createElement("div");
    notifElement.className = "toast-notification";
    notifElement.innerHTML = `
            <div class="toast-header">
                <strong class="mr-auto">${notification.title}</strong>
                <small>${this.formatTimestamp(notification.created_at)}</small>
                <button type="button" class="ml-2 mb-1 close" data-dismiss="toast" aria-label="Close">
                    <span aria-hidden="true">&times;</span>
                </button>
            </div>
            <div class="toast-body">${notification.message}</div>
        `;

    // Add notification to container
    this.container.appendChild(notifElement);

    // Show with animation
    setTimeout(() => {
      notifElement.classList.add("show");
    }, 100);

    // Setup close button and auto-close
    notifElement.querySelector(".close").addEventListener("click", () => {
      this.closeNotification(notifElement);
    });

    // Auto-close after 5 seconds
    setTimeout(() => {
      this.closeNotification(notifElement);
    }, 5000);

    // Mark as read after 3 seconds
    setTimeout(() => {
      this.markAsRead(notification.notification_id);
    }, 3000);
  }

  closeNotification(element) {
    element.classList.remove("show");
    setTimeout(() => {
      if (element.parentNode === this.container) {
        this.container.removeChild(element);
      }
    }, 300);
  }

  markAsRead(notificationId) {
    const baseUrl = this.getBaseUrl();
    fetch(baseUrl + "mark_notification_read.php", {
      method: "POST",
      headers: {
        "Content-Type": "application/x-www-form-urlencoded",
      },
      body: "notification_id=" + notificationId,
    }).catch((error) =>
      console.error("Error marking notification as read:", error)
    );
  }

  updateNotificationCount(count) {
    const badge = document.querySelector("#notificationDropdown .badge");
    if (badge) {
      if (count > 0) {
        badge.textContent = count;
        badge.style.display = "inline";
      } else {
        badge.style.display = "none";
      }
    }
  }

  formatTimestamp(timestamp) {
    const date = new Date(timestamp);
    return date.toLocaleTimeString([], { hour: "2-digit", minute: "2-digit" });
  }

  // Helper for determining the base URL
  getBaseUrl() {
    const paths = window.location.pathname.split("/");
    let baseUrl = "";

    // Check if we're in a subfolder
    if (paths.includes("owner") || paths.includes("renter")) {
      baseUrl = "../";
    }

    return baseUrl;
  }
}

// Initialize notification system when document is ready
document.addEventListener("DOMContentLoaded", function () {
  window.notificationSystem = new NotificationSystem();
});

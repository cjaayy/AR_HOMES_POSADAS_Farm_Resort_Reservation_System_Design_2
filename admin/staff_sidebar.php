<?php
/**
 * Staff sidebar include - minimal navigation for staff users
 */
// Must be included from files under admin/
?>
<?php
// Highlight active item based on current script
$current = basename($_SERVER['PHP_SELF'] ?? '');
function activeClass($file, $current) {
    return $file === $current ? 'nav-item active' : 'nav-item';
}
?>
<aside class="sidebar staff-sidebar" id="staff-sidebar">
  <nav class="sidebar-nav">
    <ul class="nav-menu">
      <li class="<?php echo activeClass('staff_dashboard.php', $current); ?>"><a href="staff_dashboard.php" class="nav-link"><i class="fas fa-tachometer-alt"></i><span>Dashboard</span></a></li>
      <li class="<?php echo activeClass('staff_reservations.php', $current); ?>"><a href="staff_reservations.php" class="nav-link"><i class="fas fa-calendar-check"></i><span>Reservations</span></a></li>
      <li class="<?php echo activeClass('staff_manage_users.php', $current); ?>"><a href="staff_manage_users.php" class="nav-link"><i class="fas fa-users"></i><span>Guests</span></a></li>
      <li class="<?php echo activeClass('staff_tasks.php', $current); ?>"><a href="staff_tasks.php" class="nav-link"><i class="fas fa-tasks"></i><span>Tasks</span></a></li>
    </ul>
  </nav>
</aside>

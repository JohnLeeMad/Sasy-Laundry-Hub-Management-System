<div class="modal fade" id="manageAnnouncementsModal" tabindex="-1" aria-labelledby="manageAnnouncementsModalLabel" aria-hidden="true">
    <div class="modal-dialog modal-lg">
        <div class="modal-content border-0 rounded-lg">
            <div class="modal-header" style="background: var(--primary-color); color: white;">
                <h5 class="modal-title" id="manageAnnouncementsModalLabel">
                    <i class="fas fa-bullhorn me-2"></i>Past Price Announcements
                </h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close" style="filter: invert(1);"></button>
            </div>
            <div class="modal-body">
                <div class="table-responsive" style="max-height: 500px; overflow-y: auto;">
                    <table class="table table-hover table-striped">
                        <thead style="background-color: var(--primary-color); color: white; position: sticky; top: 0; z-index: 10;">
                            <tr>
                                <th>Title</th>
                                <th>Type</th>
                                <th>Effective Date</th>
                                <th>Status</th>
                                <th class="text-center" style="width: 80px;">Action</th>
                            </tr>
                        </thead>
                        <tbody>
                            <?php
                            $sql_announcements = "SELECT pa.*, u.name as created_by_name 
                                                 FROM price_announcements pa
                                                 LEFT JOIN users u ON pa.created_by = u.id
                                                 ORDER BY pa.created_at DESC";
                            $announcements_result = $conn->query($sql_announcements);
                            
                            if ($announcements_result && $announcements_result->num_rows > 0):
                                while ($announcement = $announcements_result->fetch_assoc()):
                                    $isActive = ($announcement['effective_date'] <= date('Y-m-d'));
                                    $statusBadge = $isActive ? 'bg-success' : 'bg-warning';
                                    $statusText = $isActive ? 'Active' : 'Scheduled';
                                    
                                    $typeIcon = $announcement['announcement_type'] === 'price_increase' 
                                        ? '<i class="fas fa-arrow-up text-danger"></i>' 
                                        : '<i class="fas fa-arrow-down text-success"></i>';
                                    $typeText = $announcement['announcement_type'] === 'price_increase' 
                                        ? 'Increase' 
                                        : 'Decrease';
                            ?>
                                    <tr>
                                        <td>
                                            <strong><?php echo htmlspecialchars($announcement['title']); ?></strong>
                                            <?php if (!empty($announcement['message'])): ?>
                                                <br><small class="text-muted"><?php echo htmlspecialchars(substr($announcement['message'], 0, 50)) . (strlen($announcement['message']) > 50 ? '...' : ''); ?></small>
                                            <?php endif; ?>
                                        </td>
                                        <td><?php echo $typeIcon . ' ' . $typeText; ?></td>
                                        <td><?php echo date('M d, Y', strtotime($announcement['effective_date'])); ?></td>
                                        <td><span class="badge <?php echo $statusBadge; ?>"><?php echo $statusText; ?></span></td>
                                        <td class="text-center">
                                            <a href="req/delete-announcement.php?id=<?php echo $announcement['id']; ?>" 
                                               class="modern-action-btn modern-action-btn-danger" 
                                               onclick="return confirm('Are you sure you want to delete this announcement?');" 
                                               title="Delete Announcement">
                                                <i class="fas fa-trash-alt text-danger"></i>
                                            </a>
                                        </td>
                                    </tr>
                            <?php 
                                endwhile;
                            else:
                            ?>
                                <tr>
                                    <td colspan="5" class="text-center text-muted" style="padding: 2rem;">
                                        No announcements found.
                                    </td>
                                </tr>
                            <?php endif; ?>
                        </tbody>
                    </table>
                </div>
            </div>
            <div class="modal-footer">
                <button type="button" class="btn btn-secondary" data-bs-dismiss="modal"
                    style="background: #6c757d; color: white; border-color: #6c757d;">
                    Close
                </button>
            </div>
        </div>
    </div>
</div>
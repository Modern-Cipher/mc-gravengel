<?php require APPROOT . '/views/includes/admin_header.php'; ?>

<div class="row">
    <div class="col-lg-8 mx-auto">
        <div class="card p-4">
            <h2 class="mb-4">Add New Burial</h2>
            <form action="<?php echo URLROOT; ?>/admin/addBurial" method="post" style="display:none;">
                
                <h4>Deceased Information</h4>
                <div class="row mb-3">
                    <div class="col-md-6 mb-3">
                        <label for="plot_id" class="form-label">Plot ID</label>
                        <select id="plot_id" name="plot_id" class="form-control" required>
                            <option value="">Select a vacant plot</option>
                            <?php foreach ($data['plots'] as $plot): ?>
                                <option value="<?php echo htmlspecialchars($plot->id); ?>">
                                    <?php echo htmlspecialchars($plot->full_plot_number); ?>
                                </option>
                            <?php endforeach; ?>
                        </select>
                    </div>
                </div>

                <div class="row mb-3">
                    <div class="col-md-3">
                        <label for="deceased_first_name" class="form-label">First Name</label>
                        <input type="text" id="deceased_first_name" name="deceased_first_name" class="form-control" required>
                    </div>
                    <div class="col-md-3">
                        <label for="deceased_middle_name" class="form-label">Middle Name</label>
                        <input type="text" id="deceased_middle_name" name="deceased_middle_name" class="form-control">
                    </div>
                    <div class="col-md-3">
                        <label for="deceased_last_name" class="form-label">Last Name</label>
                        <input type="text" id="deceased_last_name" name="deceased_last_name" class="form-control" required>
                    </div>
                    <div class="col-md-3">
                        <label for="deceased_suffix" class="form-label">Suffix</label>
                        <input type="text" id="deceased_suffix" name="deceased_suffix" class="form-control">
                    </div>
                </div>

                <div class="row mb-3">
                    <div class="col-md-3">
                        <label for="age" class="form-label">Age</label>
                        <input type="text" id="age" name="age" class="form-control">
                    </div>
                    <div class="col-md-3">
                        <label for="sex" class="form-label">Sex</label>
                        <select id="sex" name="sex" class="form-control">
                            <option value="male">Male</option>
                            <option value="female">Female</option>
                            <option value="other">Other</option>
                        </select>
                    </div>
                    <div class="col-md-3">
                        <label for="date_born" class="form-label">Date Born</label>
                        <input type="date" id="date_born" name="date_born" class="form-control">
                    </div>
                    <div class="col-md-3">
                        <label for="date_died" class="form-label">Date Died</label>
                        <input type="date" id="date_died" name="date_died" class="form-control" required>
                    </div>
                </div>

                <div class="row mb-3">
                    <div class="col-md-6">
                        <label for="cause_of_death" class="form-label">Cause of Death</label>
                        <input type="text" id="cause_of_death" name="cause_of_death" class="form-control">
                    </div>
                    <div class="col-md-3">
                        <label for="grave_level" class="form-label">Grave Level</label>
                        <input type="text" id="grave_level" name="grave_level" class="form-control" required>
                    </div>
                    <div class="col-md-3">
                        <label for="grave_type" class="form-label">Grave Type</label>
                        <input type="text" id="grave_type" name="grave_type" class="form-control" required>
                    </div>
                </div>

                <hr class="my-4">

                <h4>Interment Right Holder & Payment</h4>
                <div class="row mb-3">
                    <div class="col-md-6 mb-3">
                        <label for="interment_full_name" class="form-label">Full Name</label>
                        <input type="text" id="interment_full_name" name="interment_full_name" class="form-control" required>
                    </div>
                    <div class="col-md-6 mb-3">
                        <label for="interment_relationship" class="form-label">Relationship</label>
                        <input type="text" id="interment_relationship" name="interment_relationship" class="form-control" required>
                    </div>
                    <div class="col-md-6 mb-3">
                        <label for="interment_contact_number" class="form-label">Contact Number</label>
                        <input type="tel" id="interment_contact_number" name="interment_contact_number" class="form-control" required>
                    </div>
                    <div class="col-md-6 mb-3">
                        <label for="interment_address" class="form-label">Address</label>
                        <input type="text" id="interment_address" name="interment_address" class="form-control" required>
                    </div>
                </div>

                <div class="row mb-3">
                    <div class="col-md-4">
                        <label for="payment_amount" class="form-label">Payment Amount</label>
                        <input type="number" id="payment_amount" name="payment_amount" class="form-control" required>
                    </div>
                    <div class="col-md-4">
                        <label for="rental_date" class="form-label">Rental Date</label>
                        <input type="date" id="rental_date" name="rental_date" class="form-control" required>
                    </div>
                    <div class="col-md-4">
                        <label for="expiry_date" class="form-label">Expiry Date</label>
                        <input type="date" id="expiry_date" name="expiry_date" class="form-control" required>
                    </div>
                </div>

                <div class="mb-3">
                    <label class="form-label">Requirements</label>
                    <div>
                        <div class="form-check form-check-inline">
                            <input class="form-check-input" type="checkbox" id="req1" name="requirements[]" value="Death Certificate with registry number">
                            <label class="form-check-label" for="req1">Death Certificate with registry number</label>
                        </div>
                        <div class="form-check form-check-inline">
                            <input class="form-check-input" type="checkbox" id="req2" name="requirements[]" value="Barangay Indigency for Burial Assistance">
                            <label class="form-check-label" for="req2">Barangay Indigency for Burial Assistance</label>
                        </div>
                        <div class="form-check form-check-inline">
                            <input class="form-check-input" type="checkbox" id="req3" name="requirements[]" value="Voter's ID">
                            <label class="form-check-label" for="req3">Voter's ID</label>
                        </div>
                        <div class="form-check form-check-inline">
                            <input class="form-check-input" type="checkbox" id="req4" name="requirements[]" value="Cedula">
                            <label class="form-check-label" for="req4">Cedula</label>
                        </div>
                        <div class="form-check form-check-inline">
                            <input class="form-check-input" type="checkbox" id="req5" name="requirements[]" value="Sulat Kahilingan">
                            <label class="form-check-label" for="req5">Sulat Kahilingan</label>
                        </div>
                    </div>
                </div>

                <div class="d-flex justify-content-end mt-4">
                    <button type="submit" class="btn btn-primary btn-lg">Submit Burial Record</button>
                </div>
            </form>
        </div>
    </div>
</div>

<div class="modal fade" id="termsModal" tabindex="-1" aria-labelledby="termsModalLabel" aria-hidden="true">
  <div class="modal-dialog modal-dialog-centered">
    <div class="modal-content">
      <div class="modal-header bg-danger text-white">
        <h5 class="modal-title" id="termsModalLabel">Terms & Conditions</h5>
        <button type="button" class="btn-close btn-close-white" data-bs-dismiss="modal" aria-label="Close"></button>
      </div>
      <div class="modal-body">
        <p>By proceeding to add a new burial record, you hereby agree to the following terms:</p>
        <ol>
          <li><strong>Accuracy of Information.</strong> You affirm that all burial details, including the deceased's identity, grave location, and rental duration, are accurate and verified to the best of your knowledge. Providing false or misleading data may result in disciplinary or legal action.</li>
          <li><strong>Authorization.</strong> You confirm that you are authorized to register or modify burial records in this system either as an official cemetery staff member or as a duly appointed representative of the family or estate of the deceased.</li>
          <li><strong>Data Privacy.</strong> All personal information entered into this system is confidential and subject to the Cemetery's Privacy Policy. Data collected will be used solely for burial registry purposes and will not be shared without proper authorization or legal mandate.</li>
          <li><strong>Burial Plot Assignment.</strong> Once a burial plot is recorded and confirmed, it is considered provisionally reserved and cannot be reassigned unless a formal archiving process is completed.</li>
        </ol>
        <div class="form-check">
          <input class="form-check-input" type="checkbox" value="" id="agreeCheck">
          <label class="form-check-label" for="agreeCheck">
            I agree to the Terms & Conditions
          </label>
        </div>
      </div>
      <div class="modal-footer">
        <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Close</button>
        <button type="button" class="btn btn-primary" id="proceedBtn" disabled>Proceed to Add Burial</button>
      </div>
    </div>
  </div>
</div>

<?php require APPROOT . '/views/includes/admin_footer.php'; ?>

<script>
document.addEventListener('DOMContentLoaded', function() {
    const termsModal = new bootstrap.Modal(document.getElementById('termsModal'));
    const form = document.querySelector('form');
    const agreeCheck = document.getElementById('agreeCheck');
    const proceedBtn = document.getElementById('proceedBtn');

    // Show the modal on page load
    termsModal.show();

    // Enable/disable the proceed button based on checkbox state
    agreeCheck.addEventListener('change', function() {
        proceedBtn.disabled = !this.checked;
    });

    // Hide the modal and show the form when proceeding
    proceedBtn.addEventListener('click', function() {
        termsModal.hide();
        form.style.display = 'block'; // Show the form after agreeing
    });
});
</script>
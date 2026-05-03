--- resources/views/submission-forms/create.blade.php
+++ resources/views/submission-forms/create.blade.php
@@ -256,6 +256,26 @@
                 </div>
 
+                <div class="form-group" id="lims_destination_container" style="display: none;">
+                  <label for="lims_destination_pages">LIMS Destination Page(s)</label>
+                  <select class="form-control select2 @error('lims_destination_pages') is-invalid @enderror"
+                          id="lims_destination_pages"
+                          name="lims_destination_pages[]"
+                          multiple>
+                    @foreach($availablePages as $page)
+                      <option value="{{ $page['value'] }}" {{ in_array($page['value'], old('lims_destination_pages', []), true) ? 'selected' : '' }}>
+                        {{ $page['label'] }}
+                      </option>
+                    @endforeach
+                  </select>
+                  @error('lims_destination_pages')
+                    <div class="invalid-feedback d-block">{{ $message }}</div>
+                  @enderror
+                  <small class="form-text text-muted">
+                    Select where this form's filled data should appear in LIMS after submission from portal.
+                  </small>
+                </div>
+
                 <div class="form-group">
                   <label for="placement_mode" class="required">How Should The Form Appear?</label>
@@ -332,6 +352,20 @@
                 </div>
 
+                <div class="form-group">
+                  <div class="form-check">
+                    <input type="checkbox" 
+                           class="form-check-input" 
+                           id="is_customer_portal_form" 
+                           name="is_customer_portal_form" 
+                           value="1" 
+                           {{ old('is_customer_portal_form', false) ? 'checked' : '' }}>
+                    <label class="form-check-label" for="is_customer_portal_form">
+                      To be filled from Customer Portal
+                    </label>
+                  </div>
+                </div>
+
                 <div class="form-group">
                   <label for="start_submission_number">Start submission from number</label>
@@ -623,6 +657,17 @@
     handleTriggerButtonSection();
   });
+
+  $('#is_customer_portal_form').on('change', function() {
+      if ($(this).is(':checked')) {
+          $('#lims_destination_container').show();
+      } else {
+          $('#lims_destination_container').hide();
+      }
+  });
+  // Trigger on load
+  $('#is_customer_portal_form').trigger('change');
 });
 </script>

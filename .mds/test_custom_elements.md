# Testing Custom Elements

## Test Steps

1. **Access Form Builder**
   - Go to a submission form
   - Click "Form Builder"
   - Verify new custom elements appear in the element types panel

2. **Add Client Select Element**
   - Drag "Client Select" element to a section
   - Configure label, placeholder, etc.
   - Save the element
   - Verify it appears in the form structure

3. **Add Sample Type Select Element**
   - Drag "Sample Type Select" element to a section
   - Configure and save
   - Verify it appears in the form structure

4. **Add Dependent Elements**
   - Add "Client Unit Select" element
   - Add "Client Contact Select" element
   - Verify they appear but show as dependent on client selection

5. **Preview Form**
   - Click "Preview" to see the form
   - Verify Client Select loads with actual client data
   - Verify Sample Type Select loads with sample types
   - Select a client and verify units and contacts load dynamically

## Expected Behavior

- Client Select should load immediately with client names
- Sample Type Select should load immediately with sample types
- Client Unit Select should be empty until a client is selected
- Client Contact Select should be empty until a client is selected
- When client changes, both unit and contact dropdowns should update

## Troubleshooting

If elements don't appear:
1. Check browser console for JavaScript errors
2. Verify database has sample data in crm_customers and sample_types tables
3. Check network tab for failed AJAX requests to /submission-forms/dynamic-options

If options don't load:
1. Check if getUserCompany() function returns valid company ID
2. Verify user has access to CRM data
3. Check if active=1 filter is excluding all records
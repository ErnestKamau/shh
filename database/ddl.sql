-- DROP SCHEMA dbo;

-- imara_lims.dbo.analysis_elements definition

-- Drop table

-- DROP TABLE imara_lims.dbo.analysis_elements GO

CREATE TABLE imara_lims.dbo.analysis_elements (
	id bigint IDENTITY(1,1) NOT NULL,
	analyte_id int NOT NULL,
	decimal_places int NULL,
	reporting_symbol nvarchar(255) COLLATE SQL_Latin1_General_CP1_CI_AS NULL,
	reporting_unit nvarchar(255) COLLATE SQL_Latin1_General_CP1_CI_AS NULL,
	non_detectable bit NULL,
	non_accredited bit NOT NULL,
	active bit NOT NULL,
	company_id int NOT NULL,
	analysis_type_id int NOT NULL,
	show_on_report bit NOT NULL,
	created_at datetime NULL,
	updated_at datetime NULL,
	equipment_id int NULL,
	[method] int NULL,
	is_manual smallint NULL,
	operator_id varchar(100) COLLATE SQL_Latin1_General_CP1_CI_AS NULL,
	significant_figures float NULL,
	lod float NULL,
	hod float NULL,
	[level] int NULL,
	CONSTRAINT PK__analysis__3213E83F75D83627 PRIMARY KEY (id)
) GO;


-- imara_lims.dbo.analysis_guides definition

-- Drop table

-- DROP TABLE imara_lims.dbo.analysis_guides GO

CREATE TABLE imara_lims.dbo.analysis_guides (
	id bigint IDENTITY(1,1) NOT NULL,
	guide_name nvarchar(255) COLLATE SQL_Latin1_General_CP1_CI_AS NOT NULL,
	analyte_id int NOT NULL,
	analysis_type_id int NOT NULL,
	value float NOT NULL,
	comments nvarchar(1024) COLLATE SQL_Latin1_General_CP1_CI_AS NULL,
	recommendations nvarchar(1024) COLLATE SQL_Latin1_General_CP1_CI_AS NULL,
	created_at datetime NULL,
	updated_at datetime NULL,
	CONSTRAINT PK__analysis__3213E83FF526E794 PRIMARY KEY (id)
) GO;


-- imara_lims.dbo.analysis_method_elements definition

-- Drop table

-- DROP TABLE imara_lims.dbo.analysis_method_elements GO

CREATE TABLE imara_lims.dbo.analysis_method_elements (
	id bigint IDENTITY(1,1) NOT NULL,
	analysis_method_id int NOT NULL,
	analyte_id int NOT NULL,
	quantity float NOT NULL,
	active bit NOT NULL,
	created_at datetime NULL,
	updated_at datetime NULL,
	company_id int NULL,
	CONSTRAINT PK__analysis__3213E83F18BFB665 PRIMARY KEY (id)
) GO;


-- imara_lims.dbo.analysis_methods definition

-- Drop table

-- DROP TABLE imara_lims.dbo.analysis_methods GO

CREATE TABLE imara_lims.dbo.analysis_methods (
	id bigint IDENTITY(1,1) NOT NULL,
	name nvarchar(255) COLLATE SQL_Latin1_General_CP1_CI_AS NOT NULL,
	code nvarchar(255) COLLATE SQL_Latin1_General_CP1_CI_AS NOT NULL,
	company_id int NOT NULL,
	description nvarchar COLLATE SQL_Latin1_General_CP1_CI_AS NULL,
	active bit NOT NULL,
	created_at datetime NULL,
	updated_at datetime NULL,
	CONSTRAINT PK__analysis__3213E83FD183C91D PRIMARY KEY (id)
) GO;


-- imara_lims.dbo.analysis_types definition

-- Drop table

-- DROP TABLE imara_lims.dbo.analysis_types GO

CREATE TABLE imara_lims.dbo.analysis_types (
	id bigint IDENTITY(1,1) NOT NULL,
	code nvarchar(255) COLLATE SQL_Latin1_General_CP1_CI_AS NOT NULL,
	name nvarchar(255) COLLATE SQL_Latin1_General_CP1_CI_AS NOT NULL,
	description nvarchar COLLATE SQL_Latin1_General_CP1_CI_AS NULL,
	sample_type_id int NOT NULL,
	lab_id int NOT NULL,
	company_id int NOT NULL,
	active bit NOT NULL,
	created_at datetime NULL,
	updated_at datetime NULL,
	short_name varchar(100) COLLATE SQL_Latin1_General_CP1_CI_AS NULL,
	reporting_time int NULL,
	CONSTRAINT PK__analysis__3213E83F7786537A PRIMARY KEY (id)
) GO;


-- imara_lims.dbo.analytes definition

-- Drop table

-- DROP TABLE imara_lims.dbo.analytes GO

CREATE TABLE imara_lims.dbo.analytes (
	id bigint IDENTITY(1,1) NOT NULL,
	code nvarchar(255) COLLATE SQL_Latin1_General_CP1_CI_AS NOT NULL,
	name nvarchar(255) COLLATE SQL_Latin1_General_CP1_CI_AS NOT NULL,
	decimal_places int NOT NULL,
	equivalent_weight float NULL,
	reporting_symbol nvarchar(255) COLLATE SQL_Latin1_General_CP1_CI_AS NULL,
	reporting_unit nvarchar(255) COLLATE SQL_Latin1_General_CP1_CI_AS NULL,
	[method] nvarchar(255) COLLATE SQL_Latin1_General_CP1_CI_AS NOT NULL,
	common_name nvarchar(255) COLLATE SQL_Latin1_General_CP1_CI_AS NULL,
	non_detectable bit NOT NULL,
	non_accredited bit NOT NULL,
	active bit NOT NULL,
	company_id int NOT NULL,
	show_on_report bit NOT NULL,
	created_at datetime NULL,
	updated_at datetime NULL,
	equipment_id nvarchar(255) COLLATE SQL_Latin1_General_CP1_CI_AS NULL,
	CONSTRAINT PK__analytes__3213E83FEBDBB218 PRIMARY KEY (id)
) GO;


-- imara_lims.dbo.approvals definition

-- Drop table

-- DROP TABLE imara_lims.dbo.approvals GO

CREATE TABLE imara_lims.dbo.approvals (
	id bigint IDENTITY(1,1) NOT NULL,
	title nvarchar(255) COLLATE SQL_Latin1_General_CP1_CI_AS NOT NULL,
	[for] nvarchar(255) COLLATE SQL_Latin1_General_CP1_CI_AS NOT NULL,
	stage nvarchar(255) COLLATE SQL_Latin1_General_CP1_CI_AS NOT NULL,
	role_id int NOT NULL,
	[level] int NOT NULL,
	created_at datetime NULL,
	updated_at datetime NULL,
	CONSTRAINT PK__approval__3213E83FE6D6F3ED PRIMARY KEY (id)
) GO;


-- imara_lims.dbo.audits definition

-- Drop table

-- DROP TABLE imara_lims.dbo.audits GO

CREATE TABLE imara_lims.dbo.audits (
	id bigint IDENTITY(1,1) NOT NULL,
	user_type nvarchar(255) COLLATE SQL_Latin1_General_CP1_CI_AS NULL,
	user_id bigint NULL,
	event nvarchar(255) COLLATE SQL_Latin1_General_CP1_CI_AS NOT NULL,
	auditable_type nvarchar(255) COLLATE SQL_Latin1_General_CP1_CI_AS NOT NULL,
	auditable_id bigint NOT NULL,
	old_values nvarchar COLLATE SQL_Latin1_General_CP1_CI_AS NULL,
	new_values nvarchar COLLATE SQL_Latin1_General_CP1_CI_AS NULL,
	url nvarchar COLLATE SQL_Latin1_General_CP1_CI_AS NULL,
	ip_address nvarchar(45) COLLATE SQL_Latin1_General_CP1_CI_AS NULL,
	user_agent nvarchar(1023) COLLATE SQL_Latin1_General_CP1_CI_AS NULL,
	tags nvarchar(255) COLLATE SQL_Latin1_General_CP1_CI_AS NULL,
	created_at datetime NULL,
	updated_at datetime NULL,
	CONSTRAINT PK__audits__3213E83F80EB4F7C PRIMARY KEY (id)
) GO
 CREATE NONCLUSTERED INDEX audits_auditable_type_auditable_id_index ON dbo.audits (  auditable_type ASC  , auditable_id ASC  )
	 WITH (  PAD_INDEX = OFF ,FILLFACTOR = 100  ,SORT_IN_TEMPDB = OFF , IGNORE_DUP_KEY = OFF , STATISTICS_NORECOMPUTE = OFF , ONLINE = OFF , ALLOW_ROW_LOCKS = ON , ALLOW_PAGE_LOCKS = ON  )
	 ON [PRIMARY ]  GO
 CREATE NONCLUSTERED INDEX audits_user_id_user_type_index ON dbo.audits (  user_id ASC  , user_type ASC  )
	 WITH (  PAD_INDEX = OFF ,FILLFACTOR = 100  ,SORT_IN_TEMPDB = OFF , IGNORE_DUP_KEY = OFF , STATISTICS_NORECOMPUTE = OFF , ONLINE = OFF , ALLOW_ROW_LOCKS = ON , ALLOW_PAGE_LOCKS = ON  )
	 ON [PRIMARY ]  GO;


-- imara_lims.dbo.batch_comments definition

-- Drop table

-- DROP TABLE imara_lims.dbo.batch_comments GO

CREATE TABLE imara_lims.dbo.batch_comments (
	id bigint IDENTITY(1,1) NOT NULL,
	comments nvarchar COLLATE SQL_Latin1_General_CP1_CI_AS NOT NULL,
	created_by int NOT NULL,
	reminder_for int NOT NULL,
	personnel_to_cc nvarchar(255) COLLATE SQL_Latin1_General_CP1_CI_AS NOT NULL,
	completed_at date NULL,
	created_at datetime NULL,
	updated_at datetime NULL,
	sample_header_id int NULL,
	comment_type varchar(255) COLLATE SQL_Latin1_General_CP1_CI_AS NULL,
	CONSTRAINT PK__batch_co__3213E83F47B58120 PRIMARY KEY (id)
) GO;


-- imara_lims.dbo.captured_results definition

-- Drop table

-- DROP TABLE imara_lims.dbo.captured_results GO

CREATE TABLE imara_lims.dbo.captured_results (
	id bigint IDENTITY(1,1) NOT NULL,
	sample_detail_code nvarchar(255) COLLATE SQL_Latin1_General_CP1_CI_AS NOT NULL,
	sample_detail_id int NOT NULL,
	sample_header_id int NOT NULL,
	analyte_id int NOT NULL,
	analyte_code nvarchar(255) COLLATE SQL_Latin1_General_CP1_CI_AS NOT NULL,
	equipment_id int NOT NULL,
	[result] float NULL,
	user_id int NOT NULL,
	created_at datetime NULL,
	updated_at datetime NULL,
	analysis_type_id int NULL,
	operator_id int NULL,
	method_id int NULL,
	machine_update_date datetime NULL,
	CONSTRAINT PK__captured__3213E83FF2A5A9D3 PRIMARY KEY (id)
) GO;


-- imara_lims.dbo.chain_of_custodies definition

-- Drop table

-- DROP TABLE imara_lims.dbo.chain_of_custodies GO

CREATE TABLE imara_lims.dbo.chain_of_custodies (
	id bigint IDENTITY(1,1) NOT NULL,
	workflow_stage nvarchar(255) COLLATE SQL_Latin1_General_CP1_CI_AS NOT NULL,
	tracking_stage_id int NOT NULL,
	moved_in_by int NOT NULL,
	moved_out_by int NULL,
	moved_out_date datetime NULL,
	sample_header_id int NOT NULL,
	comments nvarchar(1024) COLLATE SQL_Latin1_General_CP1_CI_AS NULL,
	created_at datetime NULL,
	updated_at datetime NULL,
	CONSTRAINT PK__chain_of__3213E83F84A47DC5 PRIMARY KEY (id)
) GO;


-- imara_lims.dbo.companies definition

-- Drop table

-- DROP TABLE imara_lims.dbo.companies GO

CREATE TABLE imara_lims.dbo.companies (
	id bigint IDENTITY(1,1) NOT NULL,
	name nvarchar(255) COLLATE SQL_Latin1_General_CP1_CI_AS NOT NULL,
	logo nvarchar(255) COLLATE SQL_Latin1_General_CP1_CI_AS NOT NULL,
	location nvarchar(255) COLLATE SQL_Latin1_General_CP1_CI_AS NOT NULL,
	address nvarchar(255) COLLATE SQL_Latin1_General_CP1_CI_AS NOT NULL,
	country_id int NOT NULL,
	website nvarchar(255) COLLATE SQL_Latin1_General_CP1_CI_AS NOT NULL,
	created_at datetime NULL,
	updated_at datetime NULL,
	CONSTRAINT PK__companie__3213E83FD4AA2F93 PRIMARY KEY (id)
) GO;


-- imara_lims.dbo.company_products definition

-- Drop table

-- DROP TABLE imara_lims.dbo.company_products GO

CREATE TABLE imara_lims.dbo.company_products (
	id bigint IDENTITY(1,1) NOT NULL,
	name nvarchar(255) COLLATE SQL_Latin1_General_CP1_CI_AS NOT NULL,
	crm_company_unit_id int NOT NULL,
	created_at datetime NULL,
	updated_at datetime NULL,
	active tinyint NULL,
	CONSTRAINT PK__company___3213E83F0B9A1848 PRIMARY KEY (id)
) GO;


-- imara_lims.dbo.countries definition

-- Drop table

-- DROP TABLE imara_lims.dbo.countries GO

CREATE TABLE imara_lims.dbo.countries (
	id bigint IDENTITY(1,1) NOT NULL,
	name nvarchar(255) COLLATE SQL_Latin1_General_CP1_CI_AS NOT NULL,
	iso_code_2 nvarchar(2) COLLATE SQL_Latin1_General_CP1_CI_AS NOT NULL,
	iso_code_3 nvarchar(3) COLLATE SQL_Latin1_General_CP1_CI_AS NOT NULL,
	address_format nvarchar COLLATE SQL_Latin1_General_CP1_CI_AS NOT NULL,
	postcode_required tinyint NOT NULL,
	status tinyint NOT NULL,
	created_at datetime NULL,
	updated_at datetime NULL,
	CONSTRAINT PK__countrie__3213E83F05FA57FD PRIMARY KEY (id)
) GO;


-- imara_lims.dbo.crm_company_units definition

-- Drop table

-- DROP TABLE imara_lims.dbo.crm_company_units GO

CREATE TABLE imara_lims.dbo.crm_company_units (
	id bigint IDENTITY(1,1) NOT NULL,
	name nvarchar(255) COLLATE SQL_Latin1_General_CP1_CI_AS NOT NULL,
	company_id int NOT NULL,
	crm_customer_id int NOT NULL,
	active int NOT NULL,
	created_at datetime NULL,
	updated_at datetime NULL,
	CONSTRAINT PK__crm_comp__3213E83FC00F8D9C PRIMARY KEY (id)
) GO;


-- imara_lims.dbo.crm_customer_contacts definition

-- Drop table

-- DROP TABLE imara_lims.dbo.crm_customer_contacts GO

CREATE TABLE imara_lims.dbo.crm_customer_contacts (
	id bigint IDENTITY(1,1) NOT NULL,
	first_name nvarchar(255) COLLATE SQL_Latin1_General_CP1_CI_AS NOT NULL,
	middle_name nvarchar(255) COLLATE SQL_Latin1_General_CP1_CI_AS NOT NULL,
	last_name nvarchar(255) COLLATE SQL_Latin1_General_CP1_CI_AS NOT NULL,
	job_occupation nvarchar(255) COLLATE SQL_Latin1_General_CP1_CI_AS NOT NULL,
	unit_name nvarchar(255) COLLATE SQL_Latin1_General_CP1_CI_AS NOT NULL,
	email nvarchar(255) COLLATE SQL_Latin1_General_CP1_CI_AS NOT NULL,
	telephone nvarchar(255) COLLATE SQL_Latin1_General_CP1_CI_AS NOT NULL,
	mobile nvarchar(255) COLLATE SQL_Latin1_General_CP1_CI_AS NOT NULL,
	receive_price_list bit NOT NULL,
	receive_invoice bit NOT NULL,
	receive_report bit NOT NULL,
	company_id int NOT NULL,
	crm_customer_id int NOT NULL,
	active bit NOT NULL,
	created_at datetime NULL,
	updated_at datetime NULL,
	CONSTRAINT PK__crm_cust__3213E83F1A2BEF12 PRIMARY KEY (id)
) GO;


-- imara_lims.dbo.crm_customers definition

-- Drop table

-- DROP TABLE imara_lims.dbo.crm_customers GO

CREATE TABLE imara_lims.dbo.crm_customers (
	id bigint IDENTITY(1,1) NOT NULL,
	code nvarchar(255) COLLATE SQL_Latin1_General_CP1_CI_AS NOT NULL,
	name nvarchar(255) COLLATE SQL_Latin1_General_CP1_CI_AS NOT NULL,
	postal_address nvarchar COLLATE SQL_Latin1_General_CP1_CI_AS NULL,
	physical_address nvarchar COLLATE SQL_Latin1_General_CP1_CI_AS NULL,
	fax nvarchar(255) COLLATE SQL_Latin1_General_CP1_CI_AS NULL,
	email nvarchar(255) COLLATE SQL_Latin1_General_CP1_CI_AS NOT NULL,
	telephone1 nvarchar(255) COLLATE SQL_Latin1_General_CP1_CI_AS NOT NULL,
	telephone2 nvarchar(255) COLLATE SQL_Latin1_General_CP1_CI_AS NOT NULL,
	website nvarchar(255) COLLATE SQL_Latin1_General_CP1_CI_AS NOT NULL,
	country_id int NOT NULL,
	company_id int NOT NULL,
	active bit NOT NULL,
	created_at datetime NULL,
	updated_at datetime NULL,
	unit_configurable_name varchar(100) COLLATE SQL_Latin1_General_CP1_CI_AS NULL,
	sample_point_configurable_name varchar(100) COLLATE SQL_Latin1_General_CP1_CI_AS NULL,
	product_configurable_name varchar(100) COLLATE SQL_Latin1_General_CP1_CI_AS NULL,
	CONSTRAINT PK__crm_cust__3213E83FCEC38F5A PRIMARY KEY (id)
) GO;


-- imara_lims.dbo.entity_approvals definition

-- Drop table

-- DROP TABLE imara_lims.dbo.entity_approvals GO

CREATE TABLE imara_lims.dbo.entity_approvals (
	id bigint IDENTITY(1,1) NOT NULL,
	approval_id int NOT NULL,
	model nvarchar(255) COLLATE SQL_Latin1_General_CP1_CI_AS NOT NULL,
	model_id int NOT NULL,
	user_id int NULL,
	approved_at datetime NULL,
	status nvarchar(255) COLLATE SQL_Latin1_General_CP1_CI_AS NOT NULL,
	created_at datetime NULL,
	updated_at datetime NULL,
	description nvarchar(1024) COLLATE SQL_Latin1_General_CP1_CI_AS NULL,
	CONSTRAINT PK__entity_a__3213E83FCE90594A PRIMARY KEY (id)
) GO;


-- imara_lims.dbo.entity_attachments definition

-- Drop table

-- DROP TABLE imara_lims.dbo.entity_attachments GO

CREATE TABLE imara_lims.dbo.entity_attachments (
	id bigint IDENTITY(1,1) NOT NULL,
	title nvarchar(255) COLLATE SQL_Latin1_General_CP1_CI_AS NOT NULL,
	[type] nvarchar(255) COLLATE SQL_Latin1_General_CP1_CI_AS NOT NULL,
	[file] nvarchar(255) COLLATE SQL_Latin1_General_CP1_CI_AS NOT NULL,
	description nvarchar COLLATE SQL_Latin1_General_CP1_CI_AS NOT NULL,
	model nvarchar(255) COLLATE SQL_Latin1_General_CP1_CI_AS NOT NULL,
	model_id int NOT NULL,
	created_by int NOT NULL,
	created_at datetime NULL,
	updated_at datetime NULL,
	mime varchar(100) COLLATE SQL_Latin1_General_CP1_CI_AS NULL,
	[size] varchar(100) COLLATE SQL_Latin1_General_CP1_CI_AS NULL,
	CONSTRAINT PK__entity_a__3213E83FF28170FB PRIMARY KEY (id)
) GO;


-- imara_lims.dbo.entity_notes definition

-- Drop table

-- DROP TABLE imara_lims.dbo.entity_notes GO

CREATE TABLE imara_lims.dbo.entity_notes (
	id bigint IDENTITY(1,1) NOT NULL,
	[type] nvarchar(255) COLLATE SQL_Latin1_General_CP1_CI_AS NOT NULL,
	description nvarchar COLLATE SQL_Latin1_General_CP1_CI_AS NOT NULL,
	model nvarchar(255) COLLATE SQL_Latin1_General_CP1_CI_AS NOT NULL,
	model_id int NOT NULL,
	created_by int NOT NULL,
	created_at datetime NULL,
	updated_at datetime NULL,
	CONSTRAINT PK__entity_n__3213E83F09FC0026 PRIMARY KEY (id)
) GO;


-- imara_lims.dbo.equipment definition

-- Drop table

-- DROP TABLE imara_lims.dbo.equipment GO

CREATE TABLE imara_lims.dbo.equipment (
	id int IDENTITY(1,1) NOT NULL,
	name nvarchar(255) COLLATE SQL_Latin1_General_CP1_CI_AS NOT NULL,
	equipment_number nvarchar(255) COLLATE SQL_Latin1_General_CP1_CI_AS NOT NULL,
	description nvarchar COLLATE SQL_Latin1_General_CP1_CI_AS NOT NULL,
	picture nvarchar COLLATE SQL_Latin1_General_CP1_CI_AS NOT NULL,
	make nvarchar(255) COLLATE SQL_Latin1_General_CP1_CI_AS NOT NULL,
	model nvarchar(255) COLLATE SQL_Latin1_General_CP1_CI_AS NOT NULL,
	date_purchased date NOT NULL,
	maintainance_days int NOT NULL,
	calibration_days int NOT NULL,
	active bit NOT NULL,
	created_at datetime NULL,
	updated_at datetime NULL,
	company_id int NOT NULL,
	maintainance_notification_in_days int NULL,
	calibration_notification_in_days varchar(100) COLLATE SQL_Latin1_General_CP1_CI_AS NULL,
	inventory_item_id int NULL,
	CONSTRAINT PK__equipmen__3213E83F931D30F6 PRIMARY KEY (id)
) GO;


-- imara_lims.dbo.equipment_operators definition

-- Drop table

-- DROP TABLE imara_lims.dbo.equipment_operators GO

CREATE TABLE imara_lims.dbo.equipment_operators (
	id bigint IDENTITY(1,1) NOT NULL,
	user_id int NOT NULL,
	equipment_id int NOT NULL,
	created_at datetime NULL,
	updated_at datetime NULL,
	CONSTRAINT PK__equipmen__3213E83FEA737244 PRIMARY KEY (id)
) GO;


-- imara_lims.dbo.equipment_usage definition

-- Drop table

-- DROP TABLE imara_lims.dbo.equipment_usage GO

CREATE TABLE imara_lims.dbo.equipment_usage (
	id int IDENTITY(1,1) NOT NULL,
	operator int NOT NULL,
	sample_header int NOT NULL,
	end_date datetime NULL,
	created_at datetime NULL,
	updated_at datetime NULL,
	equipment_id int NULL,
	CONSTRAINT PK__equipmen__3213E83FA0797197 PRIMARY KEY (id)
) GO;


-- imara_lims.dbo.failed_jobs definition

-- Drop table

-- DROP TABLE imara_lims.dbo.failed_jobs GO

CREATE TABLE imara_lims.dbo.failed_jobs (
	id bigint IDENTITY(1,1) NOT NULL,
	[connection] nvarchar COLLATE SQL_Latin1_General_CP1_CI_AS NOT NULL,
	queue nvarchar COLLATE SQL_Latin1_General_CP1_CI_AS NOT NULL,
	payload nvarchar COLLATE SQL_Latin1_General_CP1_CI_AS NOT NULL,
	[exception] nvarchar COLLATE SQL_Latin1_General_CP1_CI_AS NOT NULL,
	failed_at datetime NOT NULL,
	CONSTRAINT PK__failed_j__3213E83FF3E0CB6E PRIMARY KEY (id)
) GO;


-- imara_lims.dbo.inventory_categories definition

-- Drop table

-- DROP TABLE imara_lims.dbo.inventory_categories GO

CREATE TABLE imara_lims.dbo.inventory_categories (
	id bigint IDENTITY(1,1) NOT NULL,
	name nvarchar(255) COLLATE SQL_Latin1_General_CP1_CI_AS NOT NULL,
	description nvarchar COLLATE SQL_Latin1_General_CP1_CI_AS NOT NULL,
	[image] nvarchar(255) COLLATE SQL_Latin1_General_CP1_CI_AS NOT NULL,
	created_at datetime NULL,
	updated_at datetime NULL,
	company_id int NULL,
	inventory_location_id int NULL,
	CONSTRAINT PK__inventor__3213E83F487A8269 PRIMARY KEY (id)
) GO;


-- imara_lims.dbo.inventory_departments definition

-- Drop table

-- DROP TABLE imara_lims.dbo.inventory_departments GO

CREATE TABLE imara_lims.dbo.inventory_departments (
	id bigint IDENTITY(1,1) NOT NULL,
	name nvarchar(255) COLLATE SQL_Latin1_General_CP1_CI_AS NOT NULL,
	created_at datetime NULL,
	updated_at datetime NULL,
	company_id int NULL,
	module varchar(100) COLLATE SQL_Latin1_General_CP1_CI_AS NULL,
	active int NULL,
	CONSTRAINT PK__inventor__3213E83FD1F87A56 PRIMARY KEY (id)
) GO;


-- imara_lims.dbo.inventory_item_notes definition

-- Drop table

-- DROP TABLE imara_lims.dbo.inventory_item_notes GO

CREATE TABLE imara_lims.dbo.inventory_item_notes (
	id bigint IDENTITY(1,1) NOT NULL,
	inventory_item_id int NOT NULL,
	comments nvarchar COLLATE SQL_Latin1_General_CP1_CI_AS NOT NULL,
	document nvarchar(512) COLLATE SQL_Latin1_General_CP1_CI_AS NULL,
	created_at datetime NULL,
	updated_at datetime NULL,
	CONSTRAINT PK__inventor__3213E83F2D3DBB8F PRIMARY KEY (id)
) GO;


-- imara_lims.dbo.inventory_items definition

-- Drop table

-- DROP TABLE imara_lims.dbo.inventory_items GO

CREATE TABLE imara_lims.dbo.inventory_items (
	id bigint IDENTITY(1,1) NOT NULL,
	inventory_category_id int NOT NULL,
	inventory_sub_category_id int NOT NULL,
	stock_in float NOT NULL,
	stock_out float NOT NULL,
	created_by int NOT NULL,
	supplier_id int NULL,
	inventory_department_id int NOT NULL,
	edited_by int NOT NULL,
	status nvarchar(255) COLLATE SQL_Latin1_General_CP1_CI_AS NOT NULL,
	created_at datetime NULL,
	updated_at datetime NULL,
	test_score int NULL,
	inventory_location_id int NULL,
	batch_code nvarchar(255) COLLATE SQL_Latin1_General_CP1_CI_AS NULL,
	expiry date NULL,
	price float NULL,
	received_by int NULL,
	previous_batch_code varchar(100) COLLATE SQL_Latin1_General_CP1_CI_AS NULL,
	po_number varchar(100) COLLATE SQL_Latin1_General_CP1_CI_AS NULL,
	barcode varchar(100) COLLATE SQL_Latin1_General_CP1_CI_AS NULL,
	CONSTRAINT PK__inventor__3213E83F888D5151 PRIMARY KEY (id)
) GO;


-- imara_lims.dbo.inventory_location_users definition

-- Drop table

-- DROP TABLE imara_lims.dbo.inventory_location_users GO

CREATE TABLE imara_lims.dbo.inventory_location_users (
	id bigint IDENTITY(1,1) NOT NULL,
	user_id int NOT NULL,
	inventory_location_id int NOT NULL,
	created_at datetime NULL,
	updated_at datetime NULL,
	CONSTRAINT PK__inventor__3213E83FAF63F826 PRIMARY KEY (id)
) GO;


-- imara_lims.dbo.inventory_locations definition

-- Drop table

-- DROP TABLE imara_lims.dbo.inventory_locations GO

CREATE TABLE imara_lims.dbo.inventory_locations (
	id bigint IDENTITY(1,1) NOT NULL,
	name nvarchar(255) COLLATE SQL_Latin1_General_CP1_CI_AS NOT NULL,
	[level] int NOT NULL,
	inventory_location_id int NOT NULL,
	created_at datetime NULL,
	updated_at datetime NULL,
	active smallint NULL,
	company_id int NULL,
	CONSTRAINT PK__inventor__3213E83F261E23DF PRIMARY KEY (id)
) GO;


-- imara_lims.dbo.inventory_order_item_to_inventory_items definition

-- Drop table

-- DROP TABLE imara_lims.dbo.inventory_order_item_to_inventory_items GO

CREATE TABLE imara_lims.dbo.inventory_order_item_to_inventory_items (
	id bigint IDENTITY(1,1) NOT NULL,
	inventory_order_id int NOT NULL,
	inventory_item_id int NOT NULL,
	inventory_order_item_id int NOT NULL,
	created_at datetime NULL,
	updated_at datetime NULL,
	CONSTRAINT PK__inventor__3213E83F9C043DD9 PRIMARY KEY (id)
) GO;


-- imara_lims.dbo.inventory_order_items definition

-- Drop table

-- DROP TABLE imara_lims.dbo.inventory_order_items GO

CREATE TABLE imara_lims.dbo.inventory_order_items (
	id bigint IDENTITY(1,1) NOT NULL,
	inventory_order_id int NOT NULL,
	inventory_category_id int NOT NULL,
	inventory_sub_category_id int NOT NULL,
	quantity float NOT NULL,
	fulfilled bit NOT NULL,
	created_at datetime NULL,
	updated_at datetime NULL,
	CONSTRAINT PK__inventor__3213E83F08820597 PRIMARY KEY (id)
) GO;


-- imara_lims.dbo.inventory_orders definition

-- Drop table

-- DROP TABLE imara_lims.dbo.inventory_orders GO

CREATE TABLE imara_lims.dbo.inventory_orders (
	id bigint IDENTITY(1,1) NOT NULL,
	order_number nvarchar(255) COLLATE SQL_Latin1_General_CP1_CI_AS NOT NULL,
	supplier_id nvarchar(255) COLLATE SQL_Latin1_General_CP1_CI_AS NULL,
	created_by int NOT NULL,
	status nvarchar(255) COLLATE SQL_Latin1_General_CP1_CI_AS NOT NULL,
	created_at datetime NULL,
	updated_at datetime NULL,
	company_id int NOT NULL,
	comments varchar(512) COLLATE SQL_Latin1_General_CP1_CI_AS NULL,
	CONSTRAINT PK__inventor__3213E83FC5832CCB PRIMARY KEY (id)
) GO;


-- imara_lims.dbo.inventory_store_slot_contents definition

-- Drop table

-- DROP TABLE imara_lims.dbo.inventory_store_slot_contents GO

CREATE TABLE imara_lims.dbo.inventory_store_slot_contents (
	id bigint IDENTITY(1,1) NOT NULL,
	inventory_store_slot_id int NOT NULL,
	inventory_item_id int NOT NULL,
	created_at datetime NULL,
	updated_at datetime NULL,
	CONSTRAINT PK__inventor__3213E83FDA771A11 PRIMARY KEY (id)
) GO;


-- imara_lims.dbo.inventory_store_slots definition

-- Drop table

-- DROP TABLE imara_lims.dbo.inventory_store_slots GO

CREATE TABLE imara_lims.dbo.inventory_store_slots (
	id bigint IDENTITY(1,1) NOT NULL,
	name nvarchar(255) COLLATE SQL_Latin1_General_CP1_CI_AS NOT NULL,
	inventory_store_id int NOT NULL,
	created_at datetime NULL,
	updated_at datetime NULL,
	CONSTRAINT PK__inventor__3213E83F7107D705 PRIMARY KEY (id)
) GO;


-- imara_lims.dbo.inventory_stores definition

-- Drop table

-- DROP TABLE imara_lims.dbo.inventory_stores GO

CREATE TABLE imara_lims.dbo.inventory_stores (
	id bigint IDENTITY(1,1) NOT NULL,
	name nvarchar(255) COLLATE SQL_Latin1_General_CP1_CI_AS NOT NULL,
	company_id int NOT NULL,
	inventory_location_id int NOT NULL,
	created_at datetime NULL,
	updated_at datetime NULL,
	CONSTRAINT PK__inventor__3213E83F0B89C51A PRIMARY KEY (id)
) GO;


-- imara_lims.dbo.inventory_sub_categories definition

-- Drop table

-- DROP TABLE imara_lims.dbo.inventory_sub_categories GO

CREATE TABLE imara_lims.dbo.inventory_sub_categories (
	id bigint IDENTITY(1,1) NOT NULL,
	name nvarchar(255) COLLATE SQL_Latin1_General_CP1_CI_AS NOT NULL,
	description nvarchar COLLATE SQL_Latin1_General_CP1_CI_AS NOT NULL,
	[image] nvarchar(255) COLLATE SQL_Latin1_General_CP1_CI_AS NOT NULL,
	inventory_category_id int NOT NULL,
	manufacturer nvarchar(255) COLLATE SQL_Latin1_General_CP1_CI_AS NULL,
	created_at datetime NULL,
	updated_at datetime NULL,
	minimum_level float NULL,
	unit_type varchar(100) COLLATE SQL_Latin1_General_CP1_CI_AS NULL,
	unit_price float NULL,
	reporting_decimal_places smallint NULL,
	code varchar(100) COLLATE SQL_Latin1_General_CP1_CI_AS NULL,
	company_id int NULL,
	location_id int NULL,
	CONSTRAINT PK__inventor__3213E83FDECF025B PRIMARY KEY (id)
) GO;


-- imara_lims.dbo.inventory_supplier_ratings definition

-- Drop table

-- DROP TABLE imara_lims.dbo.inventory_supplier_ratings GO

CREATE TABLE imara_lims.dbo.inventory_supplier_ratings (
	id bigint IDENTITY(1,1) NOT NULL,
	supplier_id int NOT NULL,
	inventory_item_id int NOT NULL,
	rating int NOT NULL,
	title nvarchar(255) COLLATE SQL_Latin1_General_CP1_CI_AS NOT NULL,
	comments nvarchar COLLATE SQL_Latin1_General_CP1_CI_AS NOT NULL,
	rating_by int NOT NULL,
	created_at datetime NULL,
	updated_at datetime NULL,
	CONSTRAINT PK__inventor__3213E83FBC801373 PRIMARY KEY (id)
) GO;


-- imara_lims.dbo.labs definition

-- Drop table

-- DROP TABLE imara_lims.dbo.labs GO

CREATE TABLE imara_lims.dbo.labs (
	id bigint IDENTITY(1,1) NOT NULL,
	code nvarchar(255) COLLATE SQL_Latin1_General_CP1_CI_AS NOT NULL,
	name nvarchar(255) COLLATE SQL_Latin1_General_CP1_CI_AS NOT NULL,
	address nvarchar(255) COLLATE SQL_Latin1_General_CP1_CI_AS NOT NULL,
	location nvarchar(255) COLLATE SQL_Latin1_General_CP1_CI_AS NOT NULL,
	fax nvarchar(255) COLLATE SQL_Latin1_General_CP1_CI_AS NULL,
	email nvarchar(255) COLLATE SQL_Latin1_General_CP1_CI_AS NOT NULL,
	website nvarchar(255) COLLATE SQL_Latin1_General_CP1_CI_AS NULL,
	company_id int NOT NULL,
	is_external bit NOT NULL,
	phone1 nvarchar(255) COLLATE SQL_Latin1_General_CP1_CI_AS NOT NULL,
	phone2 nvarchar(255) COLLATE SQL_Latin1_General_CP1_CI_AS NULL,
	phone3 nvarchar(255) COLLATE SQL_Latin1_General_CP1_CI_AS NULL,
	active bit NOT NULL,
	created_at datetime NULL,
	updated_at datetime NULL,
	CONSTRAINT PK__labs__3213E83FC1B97D82 PRIMARY KEY (id)
) GO;


-- imara_lims.dbo.maintainance_calibration_logs definition

-- Drop table

-- DROP TABLE imara_lims.dbo.maintainance_calibration_logs GO

CREATE TABLE imara_lims.dbo.maintainance_calibration_logs (
	id int IDENTITY(1,1) NOT NULL,
	equipment_id int NOT NULL,
	service_provider nvarchar(255) COLLATE SQL_Latin1_General_CP1_CI_AS NOT NULL,
	notes nvarchar COLLATE SQL_Latin1_General_CP1_CI_AS NOT NULL,
	[type] nvarchar(255) COLLATE SQL_Latin1_General_CP1_CI_AS NOT NULL,
	[date] date NOT NULL,
	certificate nvarchar(255) COLLATE SQL_Latin1_General_CP1_CI_AS NOT NULL,
	overseen_by int NOT NULL,
	edit_by int NULL,
	created_at datetime NULL,
	updated_at datetime NULL,
	maintainance_notification_in_days int NULL,
	calibration_notification_in_days int NULL,
	CONSTRAINT PK__maintain__3213E83F51893CEE PRIMARY KEY (id)
) GO;


-- imara_lims.dbo.migrations definition

-- Drop table

-- DROP TABLE imara_lims.dbo.migrations GO

CREATE TABLE imara_lims.dbo.migrations (
	id int IDENTITY(1,1) NOT NULL,
	migration nvarchar(255) COLLATE SQL_Latin1_General_CP1_CI_AS NOT NULL,
	batch int NOT NULL,
	CONSTRAINT PK__migratio__3213E83F4ABEF853 PRIMARY KEY (id)
) GO;


-- imara_lims.dbo.module_pre_configs definition

-- Drop table

-- DROP TABLE imara_lims.dbo.module_pre_configs GO

CREATE TABLE imara_lims.dbo.module_pre_configs (
	id bigint IDENTITY(1,1) NOT NULL,
	name nvarchar(255) COLLATE SQL_Latin1_General_CP1_CI_AS NOT NULL,
	[type] nvarchar(255) COLLATE SQL_Latin1_General_CP1_CI_AS NOT NULL,
	description nvarchar(1024) COLLATE SQL_Latin1_General_CP1_CI_AS NULL,
	created_at datetime NULL,
	updated_at datetime NULL,
	module varchar(255) COLLATE SQL_Latin1_General_CP1_CI_AS NULL,
	CONSTRAINT PK__module_p__3213E83F28400073 PRIMARY KEY (id)
) GO;


-- imara_lims.dbo.naming_convension_consensuses definition

-- Drop table

-- DROP TABLE imara_lims.dbo.naming_convension_consensuses GO

CREATE TABLE imara_lims.dbo.naming_convension_consensuses (
	id bigint IDENTITY(1,1) NOT NULL,
	string_part nvarchar(255) COLLATE SQL_Latin1_General_CP1_CI_AS NOT NULL,
	integer_part nvarchar(255) COLLATE SQL_Latin1_General_CP1_CI_AS NOT NULL,
	model nvarchar(255) COLLATE SQL_Latin1_General_CP1_CI_AS NOT NULL,
	created_at datetime NULL,
	updated_at datetime NULL,
	company_id int NULL,
	CONSTRAINT PK__naming_c__3213E83F504DD15C PRIMARY KEY (id)
) GO;


-- imara_lims.dbo.password_resets definition

-- Drop table

-- DROP TABLE imara_lims.dbo.password_resets GO

CREATE TABLE imara_lims.dbo.password_resets (
	email nvarchar(255) COLLATE SQL_Latin1_General_CP1_CI_AS NOT NULL,
	token nvarchar(255) COLLATE SQL_Latin1_General_CP1_CI_AS NOT NULL,
	created_at datetime NULL
) GO
 CREATE NONCLUSTERED INDEX password_resets_email_index ON dbo.password_resets (  email ASC  )
	 WITH (  PAD_INDEX = OFF ,FILLFACTOR = 100  ,SORT_IN_TEMPDB = OFF , IGNORE_DUP_KEY = OFF , STATISTICS_NORECOMPUTE = OFF , ONLINE = OFF , ALLOW_ROW_LOCKS = ON , ALLOW_PAGE_LOCKS = ON  )
	 ON [PRIMARY ]  GO;


-- imara_lims.dbo.personnel_work_histories definition

-- Drop table

-- DROP TABLE imara_lims.dbo.personnel_work_histories GO

CREATE TABLE imara_lims.dbo.personnel_work_histories (
	id bigint IDENTITY(1,1) NOT NULL,
	department_id int NOT NULL,
	job_id int NOT NULL,
	user_id int NOT NULL,
	end_date date NULL,
	created_at datetime NULL,
	updated_at datetime NULL,
	CONSTRAINT PK__personne__3213E83F4A8E8FDF PRIMARY KEY (id)
) GO;


-- imara_lims.dbo.phone_contacts definition

-- Drop table

-- DROP TABLE imara_lims.dbo.phone_contacts GO

CREATE TABLE imara_lims.dbo.phone_contacts (
	id bigint IDENTITY(1,1) NOT NULL,
	name nvarchar(255) COLLATE SQL_Latin1_General_CP1_CI_AS NOT NULL,
	company_id int NOT NULL,
	entity_id int NOT NULL,
	entity_type nvarchar(255) COLLATE SQL_Latin1_General_CP1_CI_AS NOT NULL,
	created_at datetime NULL,
	updated_at datetime NULL,
	CONSTRAINT PK__phone_co__3213E83FF8A78359 PRIMARY KEY (id)
) GO;


-- imara_lims.dbo.report_header_details definition

-- Drop table

-- DROP TABLE imara_lims.dbo.report_header_details GO

CREATE TABLE imara_lims.dbo.report_header_details (
	id bigint IDENTITY(1,1) NOT NULL,
	title nvarchar(255) COLLATE SQL_Latin1_General_CP1_CI_AS NOT NULL,
	[to] nvarchar(255) COLLATE SQL_Latin1_General_CP1_CI_AS NOT NULL,
	cc nvarchar(255) COLLATE SQL_Latin1_General_CP1_CI_AS NOT NULL,
	[date] date NOT NULL,
	[ref] nvarchar(255) COLLATE SQL_Latin1_General_CP1_CI_AS NOT NULL,
	re nvarchar COLLATE SQL_Latin1_General_CP1_CI_AS NOT NULL,
	[for] nvarchar(255) COLLATE SQL_Latin1_General_CP1_CI_AS NOT NULL,
	sample_header_id int NOT NULL,
	specific_analyst_id int NOT NULL,
	approved_by_id int NOT NULL,
	verified_by_id int NOT NULL,
	model nvarchar(255) COLLATE SQL_Latin1_General_CP1_CI_AS NOT NULL,
	model_id int NOT NULL,
	created_at datetime NULL,
	updated_at datetime NULL,
	[from] nvarchar(255) COLLATE SQL_Latin1_General_CP1_CI_AS NULL,
	outgoing_email_body varchar(2048) COLLATE SQL_Latin1_General_CP1_CI_AS NULL,
	CONSTRAINT PK__report_h__3213E83F83018938 PRIMARY KEY (id)
) GO;


-- imara_lims.dbo.reporting_units definition

-- Drop table

-- DROP TABLE imara_lims.dbo.reporting_units GO

CREATE TABLE imara_lims.dbo.reporting_units (
	id bigint IDENTITY(1,1) NOT NULL,
	name nvarchar(255) COLLATE SQL_Latin1_General_CP1_CI_AS NOT NULL,
	active bit NOT NULL,
	created_at datetime NULL,
	updated_at datetime NULL,
	CONSTRAINT PK__reportin__3213E83F10DECB61 PRIMARY KEY (id)
) GO;


-- imara_lims.dbo.request_entities definition

-- Drop table

-- DROP TABLE imara_lims.dbo.request_entities GO

CREATE TABLE imara_lims.dbo.request_entities (
	id bigint IDENTITY(1,1) NOT NULL,
	priority nvarchar(255) COLLATE SQL_Latin1_General_CP1_CI_AS NOT NULL,
	currency nvarchar(255) COLLATE SQL_Latin1_General_CP1_CI_AS NOT NULL,
	request_code nvarchar(255) COLLATE SQL_Latin1_General_CP1_CI_AS NOT NULL,
	status nvarchar(255) COLLATE SQL_Latin1_General_CP1_CI_AS NOT NULL,
	due_date date NULL,
	parent_request nvarchar(255) COLLATE SQL_Latin1_General_CP1_CI_AS NULL,
	parent_request_id int NULL,
	request_type nvarchar(255) COLLATE SQL_Latin1_General_CP1_CI_AS NOT NULL,
	approval_count int NOT NULL,
	required_approvals int NOT NULL,
	created_by int NOT NULL,
	description nvarchar(1024) COLLATE SQL_Latin1_General_CP1_CI_AS NULL,
	created_at datetime NULL,
	updated_at datetime NULL,
	net_value decimal(18,0) NULL,
	submission_deadline datetime NULL,
	supplier_id int NULL,
	CONSTRAINT PK__request___3213E83F0084885E PRIMARY KEY (id)
) GO;


-- imara_lims.dbo.request_entity_items definition

-- Drop table

-- DROP TABLE imara_lims.dbo.request_entity_items GO

CREATE TABLE imara_lims.dbo.request_entity_items (
	id bigint IDENTITY(1,1) NOT NULL,
	request_id int NOT NULL,
	store_id int NOT NULL,
	slot_id int NOT NULL,
	inventory_sub_category_id int NOT NULL,
	quantity int NOT NULL,
	net_value decimal(8,2) NOT NULL,
	created_at datetime NULL,
	updated_at datetime NULL,
	CONSTRAINT PK__request___3213E83F96931F12 PRIMARY KEY (id)
) GO;


-- imara_lims.dbo.request_types definition

-- Drop table

-- DROP TABLE imara_lims.dbo.request_types GO

CREATE TABLE imara_lims.dbo.request_types (
	id bigint IDENTITY(1,1) NOT NULL,
	name nvarchar(255) COLLATE SQL_Latin1_General_CP1_CI_AS NOT NULL,
	visible bit NOT NULL,
	created_at datetime NULL,
	updated_at datetime NULL,
	CONSTRAINT PK__request___3213E83F0B4F7428 PRIMARY KEY (id)
) GO;


-- imara_lims.dbo.results definition

-- Drop table

-- DROP TABLE imara_lims.dbo.results GO

CREATE TABLE imara_lims.dbo.results (
	id bigint IDENTITY(1,1) NOT NULL,
	captured_result_id int NOT NULL,
	sample_detail_code nvarchar(255) COLLATE SQL_Latin1_General_CP1_CI_AS NOT NULL,
	sample_detail_id int NOT NULL,
	sample_header_id int NOT NULL,
	analyte_id int NOT NULL,
	analyte_code nvarchar(255) COLLATE SQL_Latin1_General_CP1_CI_AS NOT NULL,
	[result] float NULL,
	guide decimal(8,6) NULL,
	comments nvarchar COLLATE SQL_Latin1_General_CP1_CI_AS NULL,
	recheck bit NOT NULL,
	guide_low decimal(8,6) NULL,
	guide_high decimal(8,6) NULL,
	unit_code nvarchar(255) COLLATE SQL_Latin1_General_CP1_CI_AS NULL,
	status_code int NULL,
	reporting_symbol nvarchar(255) COLLATE SQL_Latin1_General_CP1_CI_AS NULL,
	qc bit NULL,
	correct_target decimal(8,6) NULL,
	standard_target decimal(8,6) NULL,
	recommendations nvarchar COLLATE SQL_Latin1_General_CP1_CI_AS NULL,
	initial_result decimal(8,6) NULL,
	initial_reporting_symbol nvarchar(255) COLLATE SQL_Latin1_General_CP1_CI_AS NULL,
	very_low_guide decimal(8,6) NULL,
	very_high_guide decimal(8,6) NULL,
	created_at datetime NULL,
	updated_at datetime NULL,
	analysis_type_id int NULL,
	CONSTRAINT PK__results__3213E83F27E08D41 PRIMARY KEY (id)
) GO;


-- imara_lims.dbo.roles definition

-- Drop table

-- DROP TABLE imara_lims.dbo.roles GO

CREATE TABLE imara_lims.dbo.roles (
	id bigint IDENTITY(1,1) NOT NULL,
	name nvarchar(255) COLLATE SQL_Latin1_General_CP1_CI_AS NOT NULL,
	description nvarchar COLLATE SQL_Latin1_General_CP1_CI_AS NOT NULL,
	created_at datetime NULL,
	updated_at datetime NULL,
	company_id int NULL,
	CONSTRAINT PK__roles__3213E83F7C53E412 PRIMARY KEY (id)
) GO;


-- imara_lims.dbo.sample_analysis_stages definition

-- Drop table

-- DROP TABLE imara_lims.dbo.sample_analysis_stages GO

CREATE TABLE imara_lims.dbo.sample_analysis_stages (
	id bigint IDENTITY(1,1) NOT NULL,
	name nvarchar(255) COLLATE SQL_Latin1_General_CP1_CI_AS NOT NULL,
	active bit NOT NULL,
	created_at datetime NULL,
	updated_at datetime NULL,
	company_id int NULL,
	sample_workflow varchar(100) COLLATE SQL_Latin1_General_CP1_CI_AS NULL,
	[level] int NULL,
	CONSTRAINT PK__sample_a__3213E83F8A729819 PRIMARY KEY (id)
) GO;


-- imara_lims.dbo.sample_conditions definition

-- Drop table

-- DROP TABLE imara_lims.dbo.sample_conditions GO

CREATE TABLE imara_lims.dbo.sample_conditions (
	id bigint IDENTITY(1,1) NOT NULL,
	name nvarchar(255) COLLATE SQL_Latin1_General_CP1_CI_AS NOT NULL,
	active bit NOT NULL,
	sample_type_id int NOT NULL,
	created_at datetime NULL,
	updated_at datetime NULL,
	short_name varchar(255) COLLATE SQL_Latin1_General_CP1_CI_AS NULL,
	reporting_time int NULL,
	CONSTRAINT PK__sample_c__3213E83F55982BB7 PRIMARY KEY (id)
) GO;


-- imara_lims.dbo.sample_dates definition

-- Drop table

-- DROP TABLE imara_lims.dbo.sample_dates GO

CREATE TABLE imara_lims.dbo.sample_dates (
	id bigint IDENTITY(1,1) NOT NULL,
	name nvarchar(255) COLLATE SQL_Latin1_General_CP1_CI_AS NOT NULL,
	[date] datetime NOT NULL,
	created_at datetime NULL,
	updated_at datetime NULL,
	sample_header_id bigint NULL,
	CONSTRAINT PK__sample_d__3213E83FCF9D9B62 PRIMARY KEY (id)
) GO;


-- imara_lims.dbo.sample_details definition

-- Drop table

-- DROP TABLE imara_lims.dbo.sample_details GO

CREATE TABLE imara_lims.dbo.sample_details (
	id bigint IDENTITY(1,1) NOT NULL,
	sample_code nvarchar(255) COLLATE SQL_Latin1_General_CP1_CI_AS NOT NULL,
	analysis_type_id nvarchar(255) COLLATE SQL_Latin1_General_CP1_CI_AS NOT NULL,
	sample_condition_id nvarchar(255) COLLATE SQL_Latin1_General_CP1_CI_AS NOT NULL,
	barcode nvarchar(255) COLLATE SQL_Latin1_General_CP1_CI_AS NULL,
	comments nvarchar COLLATE SQL_Latin1_General_CP1_CI_AS NULL,
	gps nvarchar(255) COLLATE SQL_Latin1_General_CP1_CI_AS NULL,
	photo_url nvarchar(512) COLLATE SQL_Latin1_General_CP1_CI_AS NULL,
	created_at datetime NULL,
	updated_at datetime NULL,
	sample_header_id bigint NOT NULL,
	sample_point_id int NULL,
	company_product_id int NULL,
	main_body varchar(8000) COLLATE SQL_Latin1_General_CP1_CI_AS NULL,
	header_body varchar(8000) COLLATE SQL_Latin1_General_CP1_CI_AS NULL,
	CONSTRAINT PK__sample_d__3213E83F2BF40918 PRIMARY KEY (id)
) GO;


-- imara_lims.dbo.sample_headers definition

-- Drop table

-- DROP TABLE imara_lims.dbo.sample_headers GO

CREATE TABLE imara_lims.dbo.sample_headers (
	id bigint IDENTITY(1,1) NOT NULL,
	batch_code nvarchar(255) COLLATE SQL_Latin1_General_CP1_CI_AS NOT NULL,
	receipt_date datetime NOT NULL,
	date_collected datetime NOT NULL,
	crm_customer_id int NOT NULL,
	crm_unit_name nvarchar(255) COLLATE SQL_Latin1_General_CP1_CI_AS NOT NULL,
	sample_type_id int NOT NULL,
	reference_number nvarchar(255) COLLATE SQL_Latin1_General_CP1_CI_AS NULL,
	status nvarchar(255) COLLATE SQL_Latin1_General_CP1_CI_AS NOT NULL,
	is_routine bit NOT NULL,
	routine_frequency float NOT NULL,
	created_at datetime NULL,
	updated_at datetime NULL,
	is_amendment int NULL,
	schedule_sent tinyint NULL,
	sample_tracking_stage int NULL,
	description nvarchar(1000) COLLATE SQL_Latin1_General_CP1_CI_AS NULL,
	document_number varchar(100) COLLATE SQL_Latin1_General_CP1_CI_AS NULL,
	importer_address nvarchar(255) COLLATE SQL_Latin1_General_CP1_CI_AS NULL,
	receiving_officer int NULL,
	sampling_officer int NULL,
	priority varchar(100) COLLATE SQL_Latin1_General_CP1_CI_AS NOT NULL,
	reason_for_submission varchar(512) COLLATE SQL_Latin1_General_CP1_CI_AS NULL,
	how_sample_was_obtained varchar(512) COLLATE SQL_Latin1_General_CP1_CI_AS NULL,
	specialist_analyst_id int NULL,
	declared_commodity_code varchar(512) COLLATE SQL_Latin1_General_CP1_CI_AS NULL,
	net_quantity_and_unit_of_quantity varchar(128) COLLATE SQL_Latin1_General_CP1_CI_AS NULL,
	sample_appearance_description varchar(512) COLLATE SQL_Latin1_General_CP1_CI_AS NULL,
	use_of_goods varchar(512) COLLATE SQL_Latin1_General_CP1_CI_AS NULL,
	kra_office_ref varchar(255) COLLATE SQL_Latin1_General_CP1_CI_AS NULL,
	kra_office_station varchar(255) COLLATE SQL_Latin1_General_CP1_CI_AS NULL,
	where_sample_was_obtained varchar(255) COLLATE SQL_Latin1_General_CP1_CI_AS NULL,
	declared_amount decimal(18,0) NULL,
	final_declared_amount decimal(18,0) NULL,
	CONSTRAINT PK__sample_h__3213E83F9996756B PRIMARY KEY (id)
) GO;


-- imara_lims.dbo.sample_points definition

-- Drop table

-- DROP TABLE imara_lims.dbo.sample_points GO

CREATE TABLE imara_lims.dbo.sample_points (
	id bigint IDENTITY(1,1) NOT NULL,
	name nvarchar(255) COLLATE SQL_Latin1_General_CP1_CI_AS NOT NULL,
	crm_company_unit_id int NOT NULL,
	created_at datetime NULL,
	updated_at datetime NULL,
	active tinyint NULL,
	CONSTRAINT PK__sample_p__3213E83F1ED145E9 PRIMARY KEY (id)
) GO;


-- imara_lims.dbo.sample_to_sample_analysis_stages definition

-- Drop table

-- DROP TABLE imara_lims.dbo.sample_to_sample_analysis_stages GO

CREATE TABLE imara_lims.dbo.sample_to_sample_analysis_stages (
	id bigint IDENTITY(1,1) NOT NULL,
	sample_type_id int NOT NULL,
	sample_analysis_stage_id int NOT NULL,
	created_at datetime NULL,
	updated_at datetime NULL,
	active smallint NULL,
	CONSTRAINT PK__sample_t__3213E83F02DABCD7 PRIMARY KEY (id)
) GO;


-- imara_lims.dbo.sample_types definition

-- Drop table

-- DROP TABLE imara_lims.dbo.sample_types GO

CREATE TABLE imara_lims.dbo.sample_types (
	id bigint IDENTITY(1,1) NOT NULL,
	code nvarchar(255) COLLATE SQL_Latin1_General_CP1_CI_AS NOT NULL,
	name nvarchar(255) COLLATE SQL_Latin1_General_CP1_CI_AS NOT NULL,
	description nvarchar COLLATE SQL_Latin1_General_CP1_CI_AS NULL,
	company_id int NOT NULL,
	active bit NOT NULL,
	created_at datetime NULL,
	updated_at datetime NULL,
	CONSTRAINT PK__sample_t__3213E83F724F7B46 PRIMARY KEY (id)
) GO;


-- imara_lims.dbo.supplier_categories definition

-- Drop table

-- DROP TABLE imara_lims.dbo.supplier_categories GO

CREATE TABLE imara_lims.dbo.supplier_categories (
	id bigint IDENTITY(1,1) NOT NULL,
	supplier_id int NOT NULL,
	inventory_sub_category_id int NOT NULL,
	created_at datetime NULL,
	updated_at datetime NULL,
	CONSTRAINT PK__supplier__3213E83FEE0F8257 PRIMARY KEY (id)
) GO;


-- imara_lims.dbo.supplier_quotes definition

-- Drop table

-- DROP TABLE imara_lims.dbo.supplier_quotes GO

CREATE TABLE imara_lims.dbo.supplier_quotes (
	id bigint IDENTITY(1,1) NOT NULL,
	supplier_id int NOT NULL,
	request_id int NOT NULL,
	request_item_id int NOT NULL,
	quote_amount float NOT NULL,
	created_at datetime NULL,
	updated_at datetime NULL,
	awarded_at datetime NULL,
	is_awarded bit NOT NULL,
	CONSTRAINT PK__supplier__3213E83F2D9EE592 PRIMARY KEY (id)
) GO;


-- imara_lims.dbo.supplier_r_f_q_s definition

-- Drop table

-- DROP TABLE imara_lims.dbo.supplier_r_f_q_s GO

CREATE TABLE imara_lims.dbo.supplier_r_f_q_s (
	id bigint IDENTITY(1,1) NOT NULL,
	supplier_id int NOT NULL,
	request_id int NOT NULL,
	rfq_sent bit NOT NULL,
	quote_received bit NOT NULL,
	created_at datetime NULL,
	updated_at datetime NULL,
	CONSTRAINT PK__supplier__3213E83FBE08F3ED PRIMARY KEY (id)
) GO;


-- imara_lims.dbo.suppliers definition

-- Drop table

-- DROP TABLE imara_lims.dbo.suppliers GO

CREATE TABLE imara_lims.dbo.suppliers (
	id int IDENTITY(1,1) NOT NULL,
	name nvarchar(255) COLLATE SQL_Latin1_General_CP1_CI_AS NOT NULL,
	logo nvarchar(255) COLLATE SQL_Latin1_General_CP1_CI_AS NOT NULL,
	email nvarchar(255) COLLATE SQL_Latin1_General_CP1_CI_AS NOT NULL,
	phone nvarchar(255) COLLATE SQL_Latin1_General_CP1_CI_AS NOT NULL,
	building nvarchar(255) COLLATE SQL_Latin1_General_CP1_CI_AS NOT NULL,
	street nvarchar(255) COLLATE SQL_Latin1_General_CP1_CI_AS NOT NULL,
	town nvarchar(255) COLLATE SQL_Latin1_General_CP1_CI_AS NOT NULL,
	address nvarchar COLLATE SQL_Latin1_General_CP1_CI_AS NOT NULL,
	created_at datetime NULL,
	updated_at datetime NULL,
	company_id int NULL,
	active smallint NOT NULL,
	inventory_location_id int NULL,
	CONSTRAINT PK__supplier__3213E83F401969A5 PRIMARY KEY (id)
) GO
 CREATE  UNIQUE NONCLUSTERED INDEX suppliers_email_unique ON dbo.suppliers (  email ASC  )
	 WITH (  PAD_INDEX = OFF ,FILLFACTOR = 100  ,SORT_IN_TEMPDB = OFF , IGNORE_DUP_KEY = OFF , STATISTICS_NORECOMPUTE = OFF , ONLINE = OFF , ALLOW_ROW_LOCKS = ON , ALLOW_PAGE_LOCKS = ON  )
	 ON [PRIMARY ]  GO
 CREATE  UNIQUE NONCLUSTERED INDEX suppliers_phone_unique ON dbo.suppliers (  phone ASC  )
	 WITH (  PAD_INDEX = OFF ,FILLFACTOR = 100  ,SORT_IN_TEMPDB = OFF , IGNORE_DUP_KEY = OFF , STATISTICS_NORECOMPUTE = OFF , ONLINE = OFF , ALLOW_ROW_LOCKS = ON , ALLOW_PAGE_LOCKS = ON  )
	 ON [PRIMARY ]  GO;


-- imara_lims.dbo.user_alerts definition

-- Drop table

-- DROP TABLE imara_lims.dbo.user_alerts GO

CREATE TABLE imara_lims.dbo.user_alerts (
	id bigint IDENTITY(1,1) NOT NULL,
	[type] nvarchar(255) COLLATE SQL_Latin1_General_CP1_CI_AS NOT NULL,
	title nvarchar(255) COLLATE SQL_Latin1_General_CP1_CI_AS NOT NULL,
	description nvarchar(255) COLLATE SQL_Latin1_General_CP1_CI_AS NOT NULL,
	url nvarchar COLLATE SQL_Latin1_General_CP1_CI_AS NOT NULL,
	user_id int NOT NULL,
	created_id int NOT NULL,
	model nvarchar(255) COLLATE SQL_Latin1_General_CP1_CI_AS NOT NULL,
	model_id int NOT NULL,
	status nvarchar(255) COLLATE SQL_Latin1_General_CP1_CI_AS NOT NULL,
	created_at datetime NULL,
	updated_at datetime NULL,
	CONSTRAINT PK__user_ale__3213E83F39AB8B27 PRIMARY KEY (id)
) GO;


-- imara_lims.dbo.user_roles definition

-- Drop table

-- DROP TABLE imara_lims.dbo.user_roles GO

CREATE TABLE imara_lims.dbo.user_roles (
	id bigint IDENTITY(1,1) NOT NULL,
	role_id int NOT NULL,
	user_id int NOT NULL,
	created_at datetime NULL,
	updated_at datetime NULL,
	CONSTRAINT PK__user_rol__3213E83FE739C5E8 PRIMARY KEY (id)
) GO;


-- imara_lims.dbo.users definition

-- Drop table

-- DROP TABLE imara_lims.dbo.users GO

CREATE TABLE imara_lims.dbo.users (
	id bigint IDENTITY(1,1) NOT NULL,
	name nvarchar(255) COLLATE SQL_Latin1_General_CP1_CI_AS NOT NULL,
	email nvarchar(255) COLLATE SQL_Latin1_General_CP1_CI_AS NOT NULL,
	email_verified_at datetime NULL,
	password nvarchar(255) COLLATE SQL_Latin1_General_CP1_CI_AS NULL,
	company_id int NOT NULL,
	remember_token nvarchar(100) COLLATE SQL_Latin1_General_CP1_CI_AS NULL,
	created_at datetime NULL,
	updated_at datetime NULL,
	active int NULL,
	location_id int NOT NULL,
	department_id int NULL,
	photo varchar(255) COLLATE SQL_Latin1_General_CP1_CI_AS NULL,
	[electronic signature] int NULL,
	[position] int NULL,
	education_level varchar(100) COLLATE SQL_Latin1_General_CP1_CI_AS NULL,
	date_of_birth date NULL,
	employment_date date NULL,
	id_number varchar(100) COLLATE SQL_Latin1_General_CP1_CI_AS NULL,
	nssf varchar(100) COLLATE SQL_Latin1_General_CP1_CI_AS NULL,
	nhif varchar(100) COLLATE SQL_Latin1_General_CP1_CI_AS NULL,
	kra_pin varchar(100) COLLATE SQL_Latin1_General_CP1_CI_AS NULL,
	first_name varchar(100) COLLATE SQL_Latin1_General_CP1_CI_AS NULL,
	middle_name varchar(100) COLLATE SQL_Latin1_General_CP1_CI_AS NULL,
	last_name varchar(100) COLLATE SQL_Latin1_General_CP1_CI_AS NULL,
	electronic_sig varchar(255) COLLATE SQL_Latin1_General_CP1_CI_AS NULL,
	designation int NULL,
	CONSTRAINT PK__users__3213E83F376A39F1 PRIMARY KEY (id)
) GO
 CREATE  UNIQUE NONCLUSTERED INDEX users_email_unique ON dbo.users (  email ASC  )
	 WITH (  PAD_INDEX = OFF ,FILLFACTOR = 100  ,SORT_IN_TEMPDB = OFF , IGNORE_DUP_KEY = OFF , STATISTICS_NORECOMPUTE = OFF , ONLINE = OFF , ALLOW_ROW_LOCKS = ON , ALLOW_PAGE_LOCKS = ON  )
	 ON [PRIMARY ]  GO;

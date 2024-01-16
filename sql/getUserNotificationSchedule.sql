SET ANSI_NULLS ON
GO
SET QUOTED_IDENTIFIER ON
GO
-- =============================================
-- Author:		NGHIANT
-- Create date: 12/03/2020
-- Description:	L?y l?ch ng??i dùng ??ng ký nhân thông báo t? ??ng
-- =============================================
ALTER PROCEDURE sp_get_user_notification_schedule 
	-- Add the parameters for the stored procedure here
	@report_type INT,
	@organization_id INT
AS
BEGIN
	-- SET NOCOUNT ON added to prevent extra result sets from
	-- interfering with SELECT statements.
	SET NOCOUNT ON;
	
	
	--TRUNCATE TABLE user_email_module;
    -- Insert statements for procedure here
	SELECT u.*, uem.params FROM users u LEFT JOIN
	( SELECT id, user_id, params FROM user_email_module WHERE report_type = @report_type AND deleted = 1) uem ON u.id = uem.user_id
	WHERE u.actived = 1 AND u.deleted = 0 AND u.organization_id = @organization_id AND uem.id IS NULL
	--@report_type
END
GO

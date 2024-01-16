CREATE TABLE send_email_details (
    id INT PRIMARY KEY NOT NULL IDENTITY(1,1),
    send_mail_id uniqueidentifier,
    mail_content NVARCHAR(MAX)
);

--DROP TABLE send_mail_details;
<?php
/**
 * Autoloader definition for the Mail component.
 *
 * @copyright Copyright (C) 2005-2008 eZ systems as. All rights reserved.
 * @license http://ez.no/licenses/new_bsd New BSD License
 * @version 1.6
 * @filesource
 * @package Mail
 */

return array(
    'ezcMailException'                  => 'Mail/exceptions/mail_exception.php',
    'ezcMailInvalidLimitException'      => 'Mail/exceptions/invalid_limit.php',
    'ezcMailNoSuchMessageException'     => 'Mail/exceptions/no_such_message.php',
    'ezcMailOffsetOutOfRangeException'  => 'Mail/exceptions/offset_out_of_range.php',
    'ezcMailTransportException'         => 'Mail/exceptions/transport_exception.php',
    'ezcMailTransportSmtpException'     => 'Mail/exceptions/transport_smtp_exception.php',
    'ezcMailPart'                       => 'Mail/interfaces/part.php',
    'ezcMailPartParser'                 => 'Mail/parser/interfaces/part_parser.php',
    'ezcMailTransport'                  => 'Mail/interfaces/transport.php',
    'ezcMail'                           => 'Mail/mail.php',
    'ezcMailFilePart'                   => 'Mail/parts/file.php',
    'ezcMailMtaTransport'               => 'Mail/transports/mta/mta_transport.php',
    'ezcMailMultipart'                  => 'Mail/parts/multipart.php',
    'ezcMailMultipartParser'            => 'Mail/parser/parts/multipart_parser.php',
    'ezcMailParserSet'                  => 'Mail/parser/interfaces/parser_set.php',
    'ezcMailSmtpTransport'              => 'Mail/transports/smtp/smtp_transport.php',
    'ezcMailTransportOptions'           => 'Mail/options/transport_options.php',
    'ezcMailAddress'                    => 'Mail/structs/mail_address.php',
    'ezcMailCharsetConverter'           => 'Mail/internal/charset_convert.php',
    'ezcMailComposer'                   => 'Mail/composer.php',
    'ezcMailComposerOptions'            => 'Mail/options/composer_options.php',
    'ezcMailContentDispositionHeader'   => 'Mail/structs/content_disposition_header.php',
    'ezcMailDeliveryStatus'             => 'Mail/parts/delivery_status.php',
    'ezcMailDeliveryStatusParser'       => 'Mail/parser/parts/delivery_status_parser.php',
    'ezcMailFile'                       => 'Mail/parts/fileparts/disk_file.php',
    'ezcMailFileParser'                 => 'Mail/parser/parts/file_parser.php',
    'ezcMailFileSet'                    => 'Mail/transports/file/file_set.php',
    'ezcMailHeaderFolder'               => 'Mail/internal/header_folder.php',
    'ezcMailHeadersHolder'              => 'Mail/parser/headers_holder.php',
    'ezcMailImapSet'                    => 'Mail/transports/imap/imap_set.php',
    'ezcMailImapSetOptions'             => 'Mail/options/imap_set_options.php',
    'ezcMailImapTransport'              => 'Mail/transports/imap/imap_transport.php',
    'ezcMailImapTransportOptions'       => 'Mail/options/imap_options.php',
    'ezcMailMboxSet'                    => 'Mail/transports/mbox/mbox_set.php',
    'ezcMailMboxTransport'              => 'Mail/transports/mbox/mbox_transport.php',
    'ezcMailMultipartAlternative'       => 'Mail/parts/multiparts/multipart_alternative.php',
    'ezcMailMultipartAlternativeParser' => 'Mail/parser/parts/multipart_alternative_parser.php',
    'ezcMailMultipartDigest'            => 'Mail/parts/multiparts/multipart_digest.php',
    'ezcMailMultipartDigestParser'      => 'Mail/parser/parts/multipart_digest_parser.php',
    'ezcMailMultipartMixed'             => 'Mail/parts/multiparts/multipart_mixed.php',
    'ezcMailMultipartMixedParser'       => 'Mail/parser/parts/multipart_mixed_parser.php',
    'ezcMailMultipartRelated'           => 'Mail/parts/multiparts/multipart_related.php',
    'ezcMailMultipartRelatedParser'     => 'Mail/parser/parts/multipart_related_parser.php',
    'ezcMailMultipartReport'            => 'Mail/parts/multiparts/multipart_report.php',
    'ezcMailMultipartReportParser'      => 'Mail/parser/parts/multipart_report_parser.php',
    'ezcMailParser'                     => 'Mail/parser/parser.php',
    'ezcMailParserOptions'              => 'Mail/options/parser_options.php',
    'ezcMailParserShutdownHandler'      => 'Mail/parser/shutdown_handler.php',
    'ezcMailPartWalkContext'            => 'Mail/structs/walk_context.php',
    'ezcMailPop3Set'                    => 'Mail/transports/pop3/pop3_set.php',
    'ezcMailPop3Transport'              => 'Mail/transports/pop3/pop3_transport.php',
    'ezcMailPop3TransportOptions'       => 'Mail/options/pop3_options.php',
    'ezcMailRfc2231Implementation'      => 'Mail/parser/rfc2231_implementation.php',
    'ezcMailRfc822Digest'               => 'Mail/parts/rfc822_digest.php',
    'ezcMailRfc822DigestParser'         => 'Mail/parser/parts/rfc822_digest_parser.php',
    'ezcMailRfc822Parser'               => 'Mail/parser/parts/rfc822_parser.php',
    'ezcMailSmtpTransportOptions'       => 'Mail/options/smtp_options.php',
    'ezcMailStorageSet'                 => 'Mail/transports/storage/storage_set.php',
    'ezcMailStreamFile'                 => 'Mail/parts/fileparts/stream_file.php',
    'ezcMailText'                       => 'Mail/parts/text.php',
    'ezcMailTextParser'                 => 'Mail/parser/parts/text_parser.php',
    'ezcMailTools'                      => 'Mail/tools.php',
    'ezcMailTransportConnection'        => 'Mail/transports/transport_connection.php',
    'ezcMailTransportMta'               => 'Mail/transports/mta/transport_mta.php',
    'ezcMailTransportSmtp'              => 'Mail/transports/smtp/transport_smtp.php',
    'ezcMailVariableSet'                => 'Mail/transports/variable/var_set.php',
    'ezcMailVirtualFile'                => 'Mail/parts/fileparts/virtual_file.php',
);
?>

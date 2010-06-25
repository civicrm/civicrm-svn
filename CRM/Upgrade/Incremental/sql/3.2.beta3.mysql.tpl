-- CRM-6400
INSERT INTO civicrm_uf_group
    (name, group_type, {localize field='title'}title{/localize}, is_reserved ) VALUES
    ('summary_overlay', 'Contact',  {localize}'Summary Overlay'{/localize}, 1 );
    
SELECT @uf_group_id_summary   := max(id) from civicrm_uf_group where name = 'summary_overlay';

INSERT INTO civicrm_uf_join
   (is_active,module,entity_table,entity_id,weight,uf_group_id) VALUES
   (1, 'Profile', NULL, NULL, 6, @uf_group_id_summary );
   
INSERT INTO civicrm_uf_field
   ( uf_group_id, field_name, is_required, is_reserved, weight, visibility, in_selector, is_searchable, location_type_id, {localize field='label'}label{/localize},field_type, help_post ) VALUES
   ( @uf_group_id_summary,           'phone' 				 ,1,  	      0, 			1, 	    'User and User Admin Only',   0, 	  0, 			 1, 				  {localize}'Home Phone'{/localize}, 					'Contact',    NULL),
   ( @uf_group_id_summary,           'phone' 				 ,1,    	  0, 			2, 	    'User and User Admin Only',   0,      0, 			 2, 				  {localize}'Home Mobile'{/localize}, 				'Contact',    NULL),
   ( @uf_group_id_summary, 			 'street_address', 		  1, 		   0, 			3, 	  	'User and User Admin Only',   0, 	  0, 			 NULL, 			   {localize}'Primary Address'{/localize},		        'Contact', 	   NULL),
   ( @uf_group_id_summary, 			 'city',				  1, 		   0, 			4, 	  	'User and User Admin Only',   0, 	  0, 			 NULL, 			   {localize}'City'{/localize},  						'Contact', 	   NULL),
   ( @uf_group_id_summary, 			 'state_province', 		  1, 		   0, 			5, 	  	'User and User Admin Only',   0, 	  0, 			 NULL, 			   {localize}'State'{/localize},  						'Contact', 	   NULL),
   ( @uf_group_id_summary, 			 'postal_code', 		  1, 		   0, 			6, 	  	'User and User Admin Only',   0, 	  0, 			 NULL, 			   {localize}'Postal Code'{/localize},  				'Contact', 	   NULL),
   ( @uf_group_id_summary, 			 'email', 				  1, 		   0, 			7, 	  	'User and User Admin Only',   0, 	  0, 			 NULL, 			   {localize}'Primary Email'{/localize},  				'Contact', 	   NULL),
   ( @uf_group_id_summary, 			 'group', 				  1, 		   0, 			8, 	  	'User and User Admin Only',   0, 	  0, 			 NULL, 			   {localize}'Groups'{/localize},  					'Contact', 	   NULL),
   ( @uf_group_id_summary, 			 'tag', 				  1, 		   0, 			9, 	  	'User and User Admin Only',   0, 	  0, 		     NULL, 			   {localize}'Tags'{/localize}, 						'Contact', 	   NULL),
   ( @uf_group_id_summary,           'gender',  			  1,  	       0,  			10,  	'User and User Admin Only',   0,  	  0,  			 NULL,  			 {localize}'Gender'{/localize},  					'Individual', NULL),
   ( @uf_group_id_summary, 		     'birth_date', 			  1,  	       0, 			11, 	'User and User Admin Only',   0, 	  0, 			 NULL, 			  {localize}'Date of Birth'{/localize}, 			    'Individual', NULL);
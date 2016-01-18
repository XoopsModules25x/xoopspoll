SmartClone Usage
================
Xoopspoll supports the SmartClone feature to be able to install multiple copies
of the Xoopspoll module.

1) To enable this support within SmartClone you must copy the
   ./xoopspoll/extras/smartclone/plugins/xoopspoll.php file into the
   ./smartclone/plugins/ folder.

2) You must run SmartClone on the entire ./xoopspoll folder including the
   following directories before installing the Xoopspoll module and prior to
   moving the files to their respective modules:
   - ./xoopspoll/extras/marquee
   - ./xoopspoll/extras/newbb_4x

   Failure to include the above directories when running SmartClone will
   prevent Xoopspoll integration with the Marquee and Newbb modules
   respectively.

3) You may not rename (clone) xoopspoll to 'umfrage'.
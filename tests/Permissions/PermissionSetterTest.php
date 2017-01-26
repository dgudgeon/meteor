<?php

namespace Meteor\Permissions;

use Exception;
use Meteor\IO\NullIO;
use Mockery;
use org\bovigo\vfs\vfsStream;

class PermissionSetterTest extends \PHPUnit_Framework_TestCase
{
    private $platform;
    private $permissionLoader;
    private $io;
    private $permissionSetter;

    public function setUp()
    {
        $this->platform = Mockery::mock('Meteor\Platform\PlatformInterface');
        $this->permissionLoader = Mockery::mock('Meteor\Permissions\PermissionLoader');
        $this->io = new NullIO();
        $this->permissionSetter = new PermissionSetter($this->platform, $this->permissionLoader, $this->io);
    }

    public function testSetDefaultPermissions()
    {
        $files = array(
            'test',
        );

        $this->platform->shouldReceive('setDefaultPermission')
            ->with('target', 'test')
            ->once();

        $this->permissionSetter->setDefaultPermissions($files, 'target');
    }

    public function testSetDefaultPermissionsCatchesExceptions()
    {
        $files = array(
            'test',
        );

        $this->platform->shouldReceive('setDefaultPermission')
            ->andThrow(new Exception())
            ->once();

        $this->permissionSetter->setDefaultPermissions($files, 'target');
    }

    public function testSetPermissions()
    {
        vfsStream::setup('root', null, array(
            'base' => array(
                'var' => array(
                    'config' => array(
                        'system.xml' => '',
                    ),
                ),
            ),
            'target' => array(
                'var' => array(
                    'config' => array(
                        'system.xml' => '',
                    ),
                ),
            ),
        ));

        $permission = new Permission('var/config');

        $this->permissionLoader->shouldReceive('load')
            ->with(vfsStream::url('root/target'))
            ->andReturn(array($permission))
            ->once();

        $this->platform->shouldReceive('setPermission')
            ->with(vfsStream::url('root/target/var/config'), $permission)
            ->once();

        $this->permissionSetter->setPermissions(vfsStream::url('root/base'), vfsStream::url('root/target'));
    }

    public function testSetPermissionsWhenPathDoesNotExistInBaseDir()
    {
        vfsStream::setup('root', null, array(
            'base' => array(
                'var' => array(),
            ),
            'target' => array(
                'var' => array(
                    'config' => array(
                        'system.xml' => '',
                    ),
                ),
            ),
        ));

        $permission = new Permission('var/config');

        $this->permissionLoader->shouldReceive('load')
            ->with(vfsStream::url('root/target'))
            ->andReturn(array($permission))
            ->once();

        $this->platform->shouldReceive('setPermission')
            ->with(vfsStream::url('root/target/var/config'), $permission)
            ->never();

        $this->permissionSetter->setPermissions(vfsStream::url('root/base'), vfsStream::url('root/target'));
    }

    public function testSetPermissionsWithWildcardPattern()
    {
        vfsStream::setup('root', null, array(
            'base' => array(
                'var' => array(
                    'config' => array(
                        'system.xml' => '',
                        'constants.xml' => '',
                    ),
                ),
            ),
            'target' => array(
                'var' => array(
                    'config' => array(
                        'system.xml' => '',
                        'constants.xml' => '',
                    ),
                ),
            ),
        ));

        $permission = new Permission('var/config/*.xml');

        $this->permissionLoader->shouldReceive('load')
            ->with(vfsStream::url('root/target'))
            ->andReturn(array($permission))
            ->once();

        $this->platform->shouldReceive('setPermission')
            ->with(vfsStream::url('root/target/var/config/system.xml'), $permission)
            ->once();

        $this->platform->shouldReceive('setPermission')
            ->with(vfsStream::url('root/target/var/config/constants.xml'), $permission)
            ->once();

        $this->permissionSetter->setPermissions(vfsStream::url('root/base'), vfsStream::url('root/target'));
    }

    public function testSetPermissionsWithWildcardPatternWhenPathDoesNotExistInBaseDir()
    {
        vfsStream::setup('root', null, array(
            'base' => array(
                'var' => array(
                    'config' => array(),
                ),
            ),
            'target' => array(
                'var' => array(
                    'config' => array(
                        'system.xml' => '',
                        'constants.xml' => '',
                    ),
                ),
            ),
        ));

        $permission = new Permission('var/config/*.xml');

        $this->permissionLoader->shouldReceive('load')
            ->with(vfsStream::url('root/target'))
            ->andReturn(array($permission))
            ->once();

        $this->platform->shouldReceive('setPermission')
            ->with(vfsStream::url('root/target/var/config/system.xml'), $permission)
            ->never();

        $this->platform->shouldReceive('setPermission')
            ->with(vfsStream::url('root/target/var/config/constants.xml'), $permission)
            ->never();

        $this->permissionSetter->setPermissions(vfsStream::url('root/base'), vfsStream::url('root/target'));
    }

    public function testSetPermissionsCatchesExceptions()
    {
        vfsStream::setup('root', null, array(
            'base' => array(
                'var' => array(
                    'config' => array(
                        'system.xml' => '',
                    ),
                ),
            ),
            'target' => array(
                'var' => array(
                    'config' => array(
                        'system.xml' => '',
                    ),
                ),
            ),
        ));

        $permission = new Permission('var/config');

        $this->permissionLoader->shouldReceive('load')
            ->with(vfsStream::url('root/target'))
            ->andReturn(array($permission))
            ->once();

        $this->platform->shouldReceive('setPermission')
            ->andThrow(new Exception())
            ->once();

        $this->permissionSetter->setPermissions(vfsStream::url('root/base'), vfsStream::url('root/target'));
    }
}

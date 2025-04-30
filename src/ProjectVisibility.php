<?php

namespace MBO\RemoteGit;

/**
 * Represents the visibility of a project.
 *
 * Note that internal is not supported by GitHub
 */
enum ProjectVisibility: string
{
    case PUBLIC = 'public';
    case PRIVATE = 'private';
    case INTERNAL = 'internal';
}
